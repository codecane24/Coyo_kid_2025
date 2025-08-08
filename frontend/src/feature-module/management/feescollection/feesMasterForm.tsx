import React, { useRef, useState, useEffect } from "react";
import { all_routes } from "../../router/all_routes";
import { Link } from "react-router-dom";
import PredefinedDateRanges from "../../../core/common/datePicker";
import CommonSelect from "../../../core/common/commonSelect";
import {
    DueDate,
  feeGroup,
  feesTypes,
  fineType,
  ids,
  status,
} from "../../../core/common/selectoption/selectoption";
import { createFeesMaster, 
          updateFeesMaster, 
          getFeesGroupList,
          getFeesTypeList,
          getFeesTypeDropdown
        } from "../../../services/FeesAllData";
import { toast } from "react-toastify";
import TooltipOption from "../../../core/common/tooltipOption";
import FeesMasterModal from "./feesMasterModal";

const FeesMasterForm = () => {
  const routes = all_routes;
  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  const [feeGroupOptions, setFeeGroupOptions] = useState<any[]>([]);
  const [feesTypeOptions, setFeesTypeOptions] = useState<any[]>([]);
  const [formData, setFormData] = useState({
    id: "",
    title: "",
    feesgroup_id: "",
    feestype_id: "",
    due_date: "",
    amount: "",
    fine_type: "",
    fine_amount: "",
    status: false,
    description: "",
  });
  // Add a loading state for dropdowns
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      getFeesGroupList().then((res) => {
        if (res && res.status === "success" && Array.isArray(res.data)) {
          setFeeGroupOptions(
            res.data.map((item: any) => ({
              value: item.id,
              label: item.name,
            }))
          );
        }
      }),
      getFeesTypeDropdown().then((result) => {
        setFeesTypeOptions(result.options || []);
      }),
    ])
    .catch((err) => {
      // Show error if dropdowns fail to load
      toast.error("Failed to load dropdown data");
    })
    .finally(() => setLoading(false));
  }, []);
  
  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleSelectChange = (field: string, option: any) => {
    setFormData({ ...formData, [field]: option?.value || "" });
  };

  const handleStatusChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, status: e.target.checked });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (formData.id) {
        await updateFeesMaster(formData.id, {
          ...formData,
          status: formData.status ? "1" : "0",
        });
        toast.success("Fees Master updated successfully");
      } else {
        await createFeesMaster({
          ...formData,
          status: formData.status ? "1" : "0",
        });
        toast.success("Fees Master added successfully");
        setFormData({
          id: "",
          title: "",
          feesgroup_id: "",
          feestype_id: "",
          due_date: "",
          amount: "",
          fine_type: "",
          fine_amount: "",
          status: false,
          description: "",
        });
      }
    } catch {
      toast.error("Failed to save Fees Master");
    }
  };

  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };
  // Add a check to render the form only if feeGroupOptions and feesTypeOptions are loaded
  const isReady =
    feeGroupOptions.length > 0 && feesTypeOptions.length > 0 && !loading;

  // Fix: Always render something, even if dropdowns are empty
  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">
        <div className="content">
          {/* Page Header */}
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Fees Master</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to="#">Fees Master</Link>
                  </li>
                  <li className="breadcrumb-item active" aria-current="page">
                   Other Charges
                  </li>
                </ol>
              </nav>
            </div>
            <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
            <TooltipOption />
              <div className="mb-2">
                <Link
                  to="#"
                  className="btn btn-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#add_fees_master"
                >
                  <i className="ti ti-square-rounded-plus me-2" />
                  Add Fees Master
                </Link>
              </div>
            </div>
          </div>
          {/* /Page Header */}
          {/* Add Fees Master Form */}
          {loading ? (
            <div className="text-center py-5">
              <span>Loading Fees Master Form...</span>
            </div>
          ) : (
            <div className="mb-4">
              <form onSubmit={handleSubmit}>
                <div className="row">
                  <div className="col-md-6 mb-3">
                    <label className="form-label">Fees Title</label>
                    <input
                      type="text"
                      className="form-control"
                      name="title"
                      value={formData.title}
                      onChange={handleChange}
                      required
                    />
                  </div>
                  <div className="col-md-6 mb-3">
                    <label className="form-label">Fees Group</label>
                    <CommonSelect
                      className="select"
                      options={feeGroupOptions}
                      value={feeGroupOptions.find(opt => opt.value === formData.feesgroup_id) || undefined}
                      onChange={option => handleSelectChange("feesgroup_id", option)}
                    />
                  </div>
                  <div className="col-md-6 mb-3">
                    <label className="form-label">Fees Type</label>
                    <CommonSelect
                      className="select"
                      options={feesTypeOptions}
                      value={feesTypeOptions.find(opt => opt.value === formData.feestype_id) || undefined}
                      onChange={option => handleSelectChange("feestype_id", option)}
                    />
                  </div>
                  <div className="col-md-6 mb-3">
                    <label className="form-label">Due Date</label>
                    <input
                      type="date"
                      className="form-control"
                      name="due_date"
                      value={formData.due_date}
                      onChange={handleChange}
                    />
                  </div>
                  <div className="col-md-6 mb-3">
                    <label className="form-label">Amount ($)</label>
                    <input
                      type="number"
                      className="form-control"
                      name="amount"
                      value={formData.amount}
                      onChange={handleChange}
                    />
                  </div>
                  <div className="col-md-6 mb-3">
                    <label className="form-label">Fine Type</label>
                    <CommonSelect
                      className="select"
                      options={fineType}
                      value={fineType.find(opt => opt.value === formData.fine_type) || undefined}
                      onChange={option => handleSelectChange("fine_type", option)}
                    />
                  </div>
                  <div className="col-md-6 mb-3">
                    <label className="form-label">Fine Amount ($)</label>
                    <input
                      type="number"
                      className="form-control"
                      name="fine_amount"
                      value={formData.fine_amount}
                      onChange={handleChange}
                    />
                  </div>
                  <div className="col-md-12 mb-3">
                    <label className="form-label">Description</label>
                    <textarea
                      className="form-control"
                      name="description"
                      value={formData.description}
                      onChange={handleChange}
                    />
                  </div>
                  <div className="col-md-12 mb-3 d-flex align-items-center justify-content-between">
                    <div className="status-title">
                      <h5>Status</h5>
                      <p>Change the Status by toggle</p>
                    </div>
                    <div className="form-check form-switch">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="switch-sm"
                        checked={formData.status}
                        onChange={handleStatusChange}
                      />
                    </div>
                  </div>
                </div>
                <div className="mt-3">
                  <button type="submit" className="btn btn-primary">
                    {formData.id ? "Update Fees Master" : "Add Fees Master"}
                  </button>
                </div>
              </form>
            </div>
          )}
        </div>
      </div>
      {/* /Page Wrapper */}
      <FeesMasterModal/>
    </>
  );
};

export default FeesMasterForm;
                 