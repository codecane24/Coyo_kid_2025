import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import CommonSelect from "../../../core/common/commonSelect";
import { feesTypes } from "../../../core/common/selectoption/selectoption";
import { DatePicker } from 'antd'
import dayjs from "dayjs";
import { toast } from "react-toastify";

import { 
  createFeesType, 
  updateFeesType, 
  deleteFeesType,
  getFeesTypeById,
  getFeesGroupList 
} from "../../../services/FeesAllData";

interface FeesModalProps {
  editType: any;
  showEditModal: boolean;
  onCloseEditModal: () => void;
  deleteId?: string | null;
  setDeleteId?: (id: string | null) => void;
  fetchFeesTypes?: () => void;
}

const FeesModal: React.FC<FeesModalProps> = ({
  editType,
  showEditModal,
  onCloseEditModal,
  deleteId,
  setDeleteId,
  fetchFeesTypes,
}) => {
  const [activeContent, setActiveContent] = useState('');
  const [feeGroupOptions, setFeeGroupOptions] = useState<any[]>([]);
  const [addType, setAddType] = useState({ name: "", feesgroup_id: "", description: "", status: false });
  const [editTypeState, setEditType] = useState<{ id?: string; name: string; feesgroup_id: string; description: string; status: boolean }>({ id: "", name: "", feesgroup_id: "", description: "", status: false });

  // Sync editType prop to local state when modal is shown or editType changes
  useEffect(() => {
    if (showEditModal && editType && editType.id) {
      // If editType is just an id, fetch full data
      if (!editType.name) {
        getFeesTypeById(editType.id).then(res => {
          if (res && res.status === "success" && res.data) {
            setEditType({
              id: res.data.id,
              name: res.data.name || "",
              feesgroup_id: res.data.feesgroup_id || "",
              description: res.data.description || "",
              status: res.data.status === "1",
            });
          }
        });
      } else {
        setEditType({
          id: editType.id,
          name: editType.name || "",
          feesgroup_id: editType.feesgroup_id || "",
          description: editType.description || "",
          status: editType.status === "1" || editType.status === true,
        });
      }
    }
  }, [editType, showEditModal]);

  const handleContentChange = (event:any) => {
      setActiveContent(event.target.value);
    };
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  const formattedDate = `${month}-${day}-${year}`
  const defaultValue = dayjs(formattedDate);
  const getModalContainer = () => {
   const modalElement = document.getElementById('modal-datepicker');
   return modalElement ? modalElement : document.body;
 };
  const getModalContainer2 = () => {
   const modalElement = document.getElementById('modal-datepicker2');
   return modalElement ? modalElement : document.body;
 };

  // Fetch dynamic fee group options
  useEffect(() => {
    getFeesGroupList().then((res) => {
      if (res && res.status === "success" && Array.isArray(res.data)) {
        setFeeGroupOptions(
          res.data.map((item: any) => ({
            value: item.id,
            label: item.name,
          }))
        );
      }
    });
  }, []);

  // Handlers for Add Fees Type
  const handleAddTypeChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setAddType({ ...addType, [e.target.name]: e.target.value });
  };
  const handleAddTypeGroup = (option: any) => {
    setAddType({ ...addType, feesgroup_id: option?.value || "" });
  };
  const handleAddTypeStatus = (e: React.ChangeEvent<HTMLInputElement>) => {
    setAddType({ ...addType, status: e.target.checked });
  };
  const handleAddTypeSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await createFeesType({
        name: addType.name,
        feesgroup_id: addType.feesgroup_id,
        description: addType.description,
        status: addType.status ? "1" : "0",
      });
      toast.success("Fees Type added successfully");
      setAddType({ name: "", feesgroup_id: "", description: "", status: false });
      // Optionally refresh table data here
    } catch {
      toast.error("Failed to add Fees Type");
    }
  };

  // Handlers for Edit Fees Type (implement fetching and setting editType as needed)
  const handleEditTypeChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setEditType({ ...editTypeState, [e.target.name]: e.target.value });
  };
  const handleEditTypeGroup = (option: any) => {
    setEditType({ ...editTypeState, feesgroup_id: option?.value || "" });
  };
  const handleEditTypeStatus = (e: React.ChangeEvent<HTMLInputElement>) => {
    setEditType({ ...editTypeState, status: e.target.checked });
  };
  // Example: When you want to set editType for editing (e.g. when opening the edit modal), make sure to include the id:
  // setEditType({ id: item.id, name: item.name, feesgroup_id: item.feesgroup_id, description: item.description, status: item.status === "1" });

  const handleEditTypeSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (!editTypeState.id) {
        toast.error("No Fees Type selected for editing.");
        return;
      }
      await updateFeesType(editTypeState.id, {
        name: editTypeState.name,
        feesgroup_id: editTypeState.feesgroup_id,
        description: editTypeState.description,
        status: editTypeState.status ? "1" : "0",
      });
      toast.success("Fees Type updated successfully");
      // Optionally refresh table data here
    } catch {
      toast.error("Failed to update Fees Type");
    }
  };

  // When opening the edit modal, always set the id property:
  const openEditTypeModal = (item: any) => {
    setEditType({
      id: item.id,
      name: item.name || "",
      feesgroup_id: item.feesgroup_id || "",
      description: item.description || "",
      status: item.status === "1",
    });
    // Show modal logic here if needed
  };

  // Delete logic for Fees Type
  const handleDelete = async () => {
    if (!deleteId) return;
    try {
      const res = await deleteFeesType(deleteId);
      if (res && res.status === "success") {
        toast.success("Fees Type deleted successfully");
        if (setDeleteId) setDeleteId(null);
        if (fetchFeesTypes) fetchFeesTypes();
      } else {
        toast.error("Failed to delete Fees Type");
        if (setDeleteId) setDeleteId(null);
      }
    } catch {
      toast.error("Failed to delete Fees Type");
      if (setDeleteId) setDeleteId(null);
    }
  };
    
  return (
    <>
    <>
  {/* Add Fees Master */}
  <div className="modal fade" id="add_fees_master">
    <div className="modal-dialog modal-dialog-centered">
      <div className="modal-content">
        <div className="modal-header">
          <div className="d-flex align-items-center">
            <h4 className="modal-title">Add Fees Master</h4>
            <span className="badge bg-soft-info ms-2">2024 - 2025</span>
          </div>
          <button
            type="button"
            className="btn-close custom-btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          >
            <i className="ti ti-x" />
          </button>
        </div>
        <form >
          <div className="modal-body" id="modal-datepicker2">
            <div className="row">
              <div className="col-md-12">
                <div className="col-md-6">
                    <div className="mb-3">
                      <label className="form-label">Fees Title</label>
                      <input type="text" className="form-control" />
                    </div>
                  </div>
                <div className="mb-3">
                  <label className="form-label">Fees Group</label>
                  <CommonSelect
                        className="select"
                        options={feeGroupOptions}
                        defaultValue={undefined}
                        />
                </div>
                {/* Fees Type */}
                {/* <div className="mb-3">
                  <label className="form-label">Fees Type</label>
                  <CommonSelect
                        className="select"
                        options={feesTypes}
                        defaultValue={undefined}
                        />
                </div> */}
              </div>
              <div className="col-md-12">
                <div className="row">
                  {/* <div className="col-md-6">
                    <div className="mb-3">
                      <label className="form-label">Due Date</label>
                      <div className="date-pic">
                      <DatePicker
                      className="form-control datetimepicker"
                      format={{
                        format: "DD-MM-YYYY",
                        type: "mask",
                      }}
                      getPopupContainer={getModalContainer2}
                      defaultValue=""
                      placeholder="Select Date"
                    />
                        <span className="cal-icon">
                          <i className="ti ti-calendar" />
                        </span>
                      </div>
                    </div>
                  </div> */}
                  {/* <div className="col-md-6">
                    <div className="mb-3">
                      <label className="form-label">Amount</label>
                      <input type="text" className="form-control" />
                    </div>
                  </div> */}
                </div>
                <div className="mb-3">
                  <label className="form-label">Fine Type</label>
                  <div className="d-flex align-items-center check-radio-group">
                    {/* <label className="custom-radio">
                      <input type="radio" name="radio" value="" checked={activeContent === ''}
                            onChange={handleContentChange} />
                      <span className="checkmark" />
                      None
                    </label> */}
                  
                    <label className="custom-radio fixed-radio">
                      <input type="radio" name="radio" value="fixed" onChange={handleContentChange} />
                      <span className="checkmark" />
                      Fixed
                    </label>
                      <label className="custom-radio percentage-radio">
                      <input type="radio" name="radio" value="percentage" onChange={handleContentChange} />
                      <span className="checkmark" />
                      Percentage
                    </label>
                  </div>
                </div>
                <div className={`percentage-field ${activeContent === 'percentage' ? 'percentage-field-show' : ''} `}>
                  <div className="row">
                    <div className="col-lg-6">
                      <div className="mb-3">
                        <label className="form-label">Percentage</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="%"
                        />
                      </div>
                    </div>
                    <div className="col-lg-6">
                      <div className="mb-3">
                        <label className="form-label">Amount ($)</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="$"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div className={`fixed-field ${activeContent === 'fixed' ? 'fixed-field-show' : ''} `}>
                  <div className="row">
                    <div className="col-lg-12">
                      <div className="mb-3">
                        <label className="form-label">Amount ($)</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="$"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div className="d-flex align-items-center justify-content-between">
                <div className="status-title">
                  <h5>Status</h5>
                  <p>Change the Status by toggle </p>
                </div>
                <div className="form-check form-switch">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="switch-sm"
                  />
                </div>
              </div>
            </div>
          </div>
          <div className="modal-footer">
            <Link to="#" className="btn btn-light me-2" data-bs-dismiss="modal">
              Cancel
            </Link>
            <Link to="#" data-bs-dismiss="modal" className="btn btn-primary">
              Add Fees Master
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
  {/* Add Fees Master*/}
  {/* Edit Fees Master */}
  <div className="modal fade" id="edit_fees_master">
    <div className="modal-dialog modal-dialog-centered">
      <div className="modal-content">
        <div className="modal-header">
          <div className="d-flex align-items-center">
            <h4 className="modal-title">Edit Fees Master</h4>
            <span className="badge bg-soft-info ms-2">2024 - 2025</span>
          </div>
          <button
            type="button"
            className="btn-close custom-btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          >
            <i className="ti ti-x" />
          </button>
        </div>
        <form>
          <div className="modal-body" id="modal-datepicker">
            <div className="row">
              <div className="col-md-12">
                <div className="mb-3">
                  <label className="form-label">Fees Group</label>
                  <CommonSelect
                        className="select"
                        options={feeGroupOptions}
                        defaultValue={feeGroupOptions[0]}
                        />
                </div>
                <div className="mb-3">
                  <label className="form-label">Fees Type</label>
                  <CommonSelect
                        className="select"
                        options={feesTypes}
                        defaultValue={feesTypes[1]}
                        />
                </div>
              </div>
              <div className="col-md-12">
                <div className="row">
                  <div className="col-md-6">
                    <div className="mb-3">
                      <label className="form-label">Due Date</label>
                      <div className="date-pic">
                      <DatePicker
                      className="form-control datetimepicker"
                      format={{
                        format: "DD-MM-YYYY",
                        type: "mask",
                      }}
                      getPopupContainer={getModalContainer}
                      defaultValue={defaultValue}
                      placeholder="16 May 2024"
                    />
                        <span className="cal-icon">
                          <i className="ti ti-calendar" />
                        </span>
                      </div>
                    </div>
                  </div>
                  <div className="col-md-6">
                    <div className="mb-3">
                      <label className="form-label">Amount</label>
                      <input
                        type="text"
                        className="form-control"
                        placeholder="Enter Amout"
                        defaultValue="$500"
                      />
                    </div>
                  </div>
                </div>
                <div className="mb-3">
                  <label className="form-label">Fine Type</label>
                  <div className="d-flex align-items-center check-radio-group">
                    <label className="custom-radio">
                      <input type="radio" name="radio" value="" checked={activeContent === ''}
                            onChange={handleContentChange} />
                      <span className="checkmark" />
                      None
                    </label>
                    <label className="custom-radio percentage-radio">
                      <input type="radio" name="radio" value="percentage" onChange={handleContentChange} />
                      <span className="checkmark" />
                      Percentage
                    </label>
                    <label className="custom-radio fixed-radio">
                      <input type="radio" name="radio" value="fixed" onChange={handleContentChange} />
                      <span className="checkmark" />
                      Fixed
                    </label>
                  </div>
                </div>
                <div className={`percentage-field ${activeContent === 'percentage' ? 'percentage-field-show' : ''} `}>
                  <div className="row">
                    <div className="col-lg-6">
                      <div className="mb-3">
                        <label className="form-label">Percentage</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="%"
                        />
                      </div>
                    </div>
                    <div className="col-lg-6">
                      <div className="mb-3">
                        <label className="form-label">Amount ($)</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="$"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div className={`fixed-field ${activeContent === 'fixed' ? 'fixed-field-show' : ''} `}>
                  <div className="row">
                    <div className="col-lg-12">
                      <div className="mb-3">
                        <label className="form-label">Amount ($)</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="$"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div className="d-flex align-items-center justify-content-between">
                <div className="status-title">
                  <h5>Status</h5>
                  <p>Change the Status by toggle </p>
                </div>
                <div className="form-check form-switch">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="switch-sm2"
                  />
                </div>
              </div>
            </div>
          </div>
          <div className="modal-footer">
            <Link to="#" className="btn btn-light me-2" data-bs-dismiss="modal">
              Cancel
            </Link>
            <Link to="#" data-bs-dismiss="modal" className="btn btn-primary">
              Save Changes
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
  {/* Edit Fees Master*/}
</>

      <>
        <div className="modal fade" id="add_fees_Type">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">Add Fees Type</h4>
                <button
                  type="button"
                  className="btn-close custom-btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                >
                  <i className="ti ti-x" />
                </button>
              </div>
              <form onSubmit={handleAddTypeSubmit}>
                <div className="modal-body">
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Name</label>
                        <input
                          type="text"
                          className="form-control"
                          name="name"
                          value={addType.name}
                          onChange={handleAddTypeChange}
                          required
                        />
                      </div>
                      <div className="mb-3">
                        <div className="d-flex justify-content-between">
                          <label className="form-label">Fees Group</label>
                          <Link
                            to="#"
                            className="text-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#add_new_fees_group"
                          >
                            <span>
                              <i className="ti ti-square-rounded-plus-filled" />
                            </span>{" "}
                            Add New
                          </Link>
                        </div>
                        <CommonSelect
                          className="select"
                          options={feeGroupOptions}
                          value={feeGroupOptions.find(opt => opt.value === addType.feesgroup_id) || null}
                          onChange={handleAddTypeGroup}
                          
                        />
                      </div>
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Description</label>
                      <textarea
                        className="form-control"
                        rows={4}
                        name="description"
                        value={addType.description}
                        onChange={handleAddTypeChange}
                        placeholder="Add Comment"
                      />
                    </div>
                    <div className="d-flex align-items-center justify-content-between">
                      <div className="status-title">
                        <h5>Status</h5>
                        <p>Change the Status by toggle </p>
                      </div>
                      <div className="form-check form-switch">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          role="switch"
                          id="switch-sm"
                          checked={addType.status}
                          onChange={handleAddTypeStatus}
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div className="modal-footer">
                  <Link
                    to="#"
                    className="btn btn-light me-2"
                    data-bs-dismiss="modal"
                  >
                    Cancel
                  </Link>
                  <button
                    type="submit"
                    className="btn btn-primary"
                    data-bs-dismiss="modal"
                  >
                    Add Fees Type
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        {/* Edit Fees Type */}
        <div className="modal fade" id="edit_fees_Type">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">Edit Fees Type</h4>
                <button
                  type="button"
                  className="btn-close custom-btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                >
                  <i className="ti ti-x" />
                </button>
              </div>
              <form onSubmit={handleEditTypeSubmit}>
                <div className="modal-body">
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Name</label>
                        <input
                          type="text"
                          className="form-control"
                          name="name"
                          value={editTypeState.name}
                          onChange={handleEditTypeChange}
                          placeholder="Enter Name"
                          required
                        />
                      </div>
                      <div className="mb-3">
                        <div className="d-flex justify-content-between">
                          <label className="form-label">Fees Group</label>
                          <Link
                            to="#"
                            className="text-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#add_new_fees_group"
                          >
                            <span>
                              <i className="ti ti-square-rounded-plus-filled" />
                            </span>{" "}
                            Add New
                          </Link>
                        </div>
                        <CommonSelect
                          className="select"
                          options={feeGroupOptions}
                          value={
                            feeGroupOptions.find(
                              opt => String(opt.value) === String(editTypeState.feesgroup_id)
                            ) || null
                          }
                          onChange={handleEditTypeGroup}
                        />
                      </div>
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Description</label>
                      <textarea
                        className="form-control"
                        rows={4}
                        name="description"
                        value={editTypeState.description}
                        onChange={handleEditTypeChange}
                        placeholder="Add Comment"
                      />
                    </div>
                    <div className="d-flex align-items-center justify-content-between">
                      <div className="status-title">
                        <h5>Status</h5>
                        <p>Change the Status by toggle </p>
                      </div>
                      <div className="form-check form-switch">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          role="switch"
                          id="switch-sm2"
                          checked={!!editTypeState.status}
                          onChange={handleEditTypeStatus}
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div className="modal-footer">
                  <Link
                    to="#"
                    className="btn btn-light me-2"
                    data-bs-dismiss="modal"
                  >
                    Cancel
                  </Link>
                  <button
                    type="submit"
                    className="btn btn-primary"
                    data-bs-dismiss="modal"
                  >
                    Save Changes
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* Edit Fees Type */}
        {/* Add New Fees Type */}
        <div className="modal fade" id="add_new_fees_group">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">Add New Fees Group</h4>
                <button
                  type="button"
                  className="btn-close custom-btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                >
                  <i className="ti ti-x" />
                </button>
              </div>
              <form>
                <div className="modal-body">
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Name</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="Enter Name"
                          defaultValue=""
                        />
                      </div>
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Description</label>
                      <textarea
                        className="form-control"
                        rows={4}
                        placeholder="Add Comment"
                        defaultValue={""}
                      />
                    </div>
                    <div className="d-flex align-items-center justify-content-between">
                      <div className="status-title">
                        <h5>Status</h5>
                        <p>Change the Status by toggle </p>
                      </div>
                      <div className="form-check form-switch">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          role="switch"
                          id="switch-sm3"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                <div className="modal-footer">
                  <Link
                    to="#"
                    className="btn btn-light me-2"
                    data-bs-dismiss="modal"
                  >
                    Cancel
                  </Link>
                  <Link
                    to="#"
                    data-bs-dismiss="modal"
                    className="btn btn-primary"
                  >
                    Add Fees Type
                  </Link>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* Add New Fees Type */}
      </>

      <>
        {/* Add Fees Group */}
        <div className="modal fade" id="add_fees_group">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">Add Fees Group</h4>
                <button
                  type="button"
                  className="btn-close custom-btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                >
                  <i className="ti ti-x" />
                </button>
              </div>
              <form>
                <div className="modal-body">
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Fees Group</label>
                        <input type="text" className="form-control" />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Description</label>
                        <textarea
                          className="form-control"
                          rows={4}
                          defaultValue={""}
                        />
                      </div>
                      <div className="d-flex align-items-center justify-content-between">
                        <div className="status-title">
                          <h5>Status</h5>
                          <p>Change the Status by toggle </p>
                        </div>
                        <div className="form-check form-switch">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="switch-sm"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="modal-footer">
                  <Link
                    to="#"
                    className="btn btn-light me-2"
                    data-bs-dismiss="modal"
                  >
                    Cancel
                  </Link>
                  <Link
                    to="#"
                    data-bs-dismiss="modal"
                    className="btn btn-primary"
                  >
                    Add Fees Group
                  </Link>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* Add Fees Group */}
        {/* Edit Fees Group */}
        <div className="modal fade" id="edit_fees_group">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">Edit Fees Group</h4>
                <button
                  type="button"
                  className="btn-close custom-btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                >
                  <i className="ti ti-x" />
                </button>
              </div>
              <form>
                <div className="modal-body">
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Fees Group</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="Enter Fees Group"
                          defaultValue="Tuition Fees"
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Description</label>
                        <textarea
                          className="form-control"
                          rows={4}
                          placeholder="Add Comment"
                          defaultValue={"The money that you pay to be taught"}
                        />
                      </div>
                      <div className="d-flex align-items-center justify-content-between">
                        <div className="status-title">
                          <h5>Status</h5>
                          <p>Change the Status by toggle </p>
                        </div>
                        <div className="form-check form-switch">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="switch-sm2"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="modal-footer">
                  <Link
                    to="#"
                    className="btn btn-light me-2"
                    data-bs-dismiss="modal"
                  >
                    Cancel
                  </Link>
                  <Link
                    to="#"
                    data-bs-dismiss="modal"
                    className="btn btn-primary"
                  >
                    Save Changes
                  </Link>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* Edit Fees Group */}
      </>

      {/* Delete Modal */}
      <div className="modal fade" id="delete-modal">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <form
              onSubmit={e => {
                e.preventDefault();
                handleDelete();
              }}
            >
              <div className="modal-body text-center">
                <span className="delete-icon">
                  <i className="ti ti-trash-x" />
                </span>
                <h4>Confirm Deletion</h4>
                <p>
                  You want to delete this Fees Type, this can't be undone once you delete.
                </p>
                <div className="d-flex justify-content-center">
                  <Link
                    to="#"
                    className="btn btn-light me-3"
                    data-bs-dismiss="modal"
                    onClick={() => setDeleteId && setDeleteId(null)}
                  >
                    Cancel
                  </Link>
                  <button
                    type="submit"
                    className="btn btn-danger"
                    data-bs-dismiss="modal"
                  >
                    Yes, Delete
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      {/* /Delete Modal */}
    </>
  );
};

export default FeesModal;
