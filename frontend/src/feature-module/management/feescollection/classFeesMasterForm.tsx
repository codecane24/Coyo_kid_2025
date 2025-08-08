import React, { useRef, useState, useEffect } from "react";
import { all_routes } from "../../router/all_routes";
import { Link } from "react-router-dom";
import TooltipOption from "../../../core/common/tooltipOption";
import { getFeesTypeDropdown,getFeesGroupList,createClassFeesMaster,updateClassFeesMaster } from "../../../services/FeesAllData";
import { getClassesList } from "../../../services/ClassData";
import { toast } from "react-toastify";
import Select from "react-select";
import { amount } from "../../../core/common/selectoption/selectoption";

const ClassFeesMasterForm = () => {
  const [classOptions, setClassOptions] = useState<any[]>([]);
  const [selectedClasses, setSelectedClasses] = useState<string[]>([]);
  const [feesTypeOptions, setFeesTypeOptions] = useState<any[]>([]);
  const [selectedFeesType, setSelectedFeesType] = useState<string>("");
  const [feeGroupName, setFeeGroupName] = useState<string>("");
  const [feeAmount, setFeeAmount] = useState<string>("");
  const [feesList, setFeesList] = useState<any[]>([]);
  const [feesTypeGroupName, setFeesTypeGroupName] = useState<string>("");
  const [feeGroupOptions, setFeeGroupOptions] = useState<any[]>([]);

  // Dynamic class options from API
  useEffect(() => {
    getClassesList().then((res: any) => {
      if (res && Array.isArray(res)) {
        setClassOptions(
          res.map((item: any) => ({
            value: item.id,
            label: item.name + (item.section ? ` (${item.section})` : ""),
          }))
        );
      }
    });
  }, []);

  // Dynamic fees type options from API
  useEffect(() => {
    getFeesTypeDropdown().then(({ options }) => {
      setFeesTypeOptions(options);
    });
  }, []);

  // Dynamic fees group options from API
  useEffect(() => {
    getFeesGroupList().then((res) => {
      if (res && res.status === "success" && Array.isArray(res.data)) {
        setFeeGroupOptions(
          res.data.map((item: any) => ({
            value: item.id,
            label: item.name,
            groupName: item.feesgroup?.name || "", // Use optional chaining to avoid error
          }))
        );
      }
    });
  }, []);

  // Show related feestype.feegsroup.name in groupInfo on fees type change
  useEffect(() => {
    if (selectedFeesType && feesTypeOptions.length > 0) {
      const selectedType = feesTypeOptions.find(opt => String(opt.value) === String(selectedFeesType));
      let groupName = "";
      if (selectedType?.group_id && feeGroupOptions.length > 0) {
        const groupObj = feeGroupOptions.find(g => String(g.value) === String(selectedType.group_id));
        groupName = groupObj?.label || "";
      }
      setFeesTypeGroupName(groupName);
    } else {
      setFeesTypeGroupName("");
    }
  }, [selectedFeesType, feesTypeOptions, feeGroupOptions]);
  const handleAddFee = () => {
    if (!selectedFeesType || !feeAmount) {
      toast.error("Select fees type and enter amount");
      return;
    }
    const selectedType = feesTypeOptions.find(opt => String(opt.value) === String(selectedFeesType));
   
    let typeName = "";
    let groupName = "";
    if (selectedType) {
      // Use label or name depending on your API response
      typeName = selectedType.label || selectedType.name || "";
      // Debug logs to help trace the issue
      console.log("selectedType.group_id:", selectedType.group_id);
      console.log("feeGroupOptions:", feeGroupOptions);
      if (selectedType.group_id && feeGroupOptions.length > 0) {
        const groupObj = feeGroupOptions.find(
          g => String(g.value) === String(selectedType.group_id)
        );
        console.log("groupObj found:", groupObj);
        groupName = groupObj?.label || groupObj?.name || "";
        // If still blank, try to fallback to groupName property
        if (!groupName && groupObj?.groupName) {
          groupName = groupObj.groupName;
        }
      }
    }


    setFeesList([
      ...feesList,
      {
        id: feesList.length + 1,
        type: typeName,
        group: groupName,
        amount: Number(feeAmount),
      },
    ]);
    setSelectedFeesType("");
    setFeeAmount("");
  };

  const handleEditFee = (idx: number) => {
    const fee = feesList[idx];
    setSelectedFeesType(
      feesTypeOptions.find(opt => opt.label === fee.type)?.value || ""
    );
    setFeeGroupName(fee.group);
    setFeeAmount(String(fee.amount));
    // Remove from list for editing
    setFeesList(feesList.filter((_, i) => i !== idx));
  };

  const handleRemoveFee = (idx: number) => {
    setFeesList(feesList.filter((_, i) => i !== idx));
  };

  const handleCheckboxChange = (idx: number) => {
    setFeesList(
      feesList.map((fee, i) =>
        i === idx ? { ...fee, checked: !fee.checked } : fee
      )
    );
  };

  const totalAmount = feesList.reduce((sum, fee) => sum + (fee.amount || 0), 0);

  // Submit handler for create/update
  const handleSubmit = async () => {
    if (selectedClasses.length === 0) {
      toast.error("Please select at least one class.");
      return;
    }
    if (feesList.length === 0) {
      toast.error("Please add at least one fee type.");
      return;
    }
    const payload = {
      classid: selectedClasses,
      feestypes: feesList.map(fee => ({
        feestype_id: feesTypeOptions.find(opt => opt.label === fee.type)?.value || "",
        amount: fee.amount
      }))
    };
    try {
      // You can add logic to decide between create/update
      // For now, always create
      const res = await createClassFeesMaster(payload);
      if (res && res.status === "success") {
        toast.success("Class Fees Master created successfully!");
        // Optionally reset form here
        setSelectedClasses([]);
        setFeesList([]);
      } else {
        toast.error(res?.message || "Failed to create Class Fees Master.");
      }
    } catch (err) {
      toast.error("API error. Please try again.");
    }
  };

  return (
    <>
      <div className="page-wrapper">
        <div className="content">
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Fees Collection</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={all_routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to="#">Fees Collection</Link>
                  </li>
                  <li className="breadcrumb-item active" aria-current="page">
                    Fees Group
                  </li>
                </ol>
              </nav>
            </div>
            <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
              <TooltipOption />
              <div className="mb-2">
                <button
                  className="btn btn-primary"
                >
                  <i className="ti ti-square-rounded-plus me-2" />
                  Add Class Fees
                </button>
              </div>
            </div>
          </div>
          <div className="card p-4">
            <h4 className="mb-3">Class Fees Master</h4>

            <div className="row mb-3">
              <div className="col-md-3">
                <label>Select Class (multiple)</label>
              </div>
              <div className="col-md-6">
                <Select
                  isMulti
                  options={classOptions}
                  value={classOptions.filter(opt => selectedClasses.includes(opt.value))}
                  onChange={selected => {
                    setSelectedClasses(Array.isArray(selected) ? selected.map(opt => opt.value) : []);
                  }}
                  placeholder="Select Class"
                  classNamePrefix="react-select"
                  isSearchable={true}
                />
              </div>
            </div>
            <div className="row mb-3">
              <div className="col-md-3">
                <label>Fees Type</label>
              </div>
              <div className="col-md-6">
                <select
                  className="form-select"
                  value={selectedFeesType}
                  onChange={e => setSelectedFeesType(e.target.value)}
                >
                  <option value="">Select Fees Type</option>
                  {feesTypeOptions.map(opt => (
                    <option 
                      key={opt.value} 
                      value={opt.value}
                    >
                      {opt.label}
                    </option>
                  ))}
                </select>
                <div className="groupInfo">
                  {feesTypeGroupName}
                </div>
              </div>
              <div className="row mb-3">
                <div className="col-md-3">
                  <label>Fee Amount</label>
                </div>
                <div className="col-md-3 mt-3">
                  <input
                    type="number"
                    className="form-control"
                    value={feeAmount}
                    onChange={e => setFeeAmount(e.target.value)}
                    placeholder="Enter Amount"
                  />
                </div>
                <div className="col-md-2 mt-4">
                  <button
                    type="button"
                    className="btn btn-success"
                    onClick={handleAddFee}
                  >
                    Add
                  </button>
                </div>
              </div>  
            </div>
            <h5 className="mt-4">Fees Description</h5>
            <table className="table table-bordered">
              <thead>
                <tr>
                  <th className="col-1">s.no</th>
                  <th>Fees Type Name</th>
                  <th>Fees Type Group</th>
                  <th>Amount</th>
                  <th>Remove</th>
                </tr>
              </thead>
              <tbody>
                {feesList.map((fee, idx) => (
                  <tr key={idx}>
                    <td>
                      <input
                        type="checkbox"
                        checked={!!fee.checked}
                        onChange={() => handleCheckboxChange(idx)}
                      />{" "}
                      {idx + 1}
                    </td>
                    <td>{fee.type}</td>
                    <td>{fee.group}</td>
                    <td>{fee.amount}</td>
                    <td>
                      <i
                        className="me-2 ti ti-trash text-danger"
                        onClick={() => handleRemoveFee(idx)}
                      ></i>
                      <i
                        className="ti ti-edit text-primary"
                        onClick={() => handleEditFee(idx)}
                      ></i>
                    </td>
                  </tr>
                ))}
                {feesList.length === 0 && (
                  <tr>
                    <td colSpan={5} className="text-center">
                      No fees added.
                    </td>
                  </tr>
                )}
              </tbody>
              <tfoot>
                <tr>
                  <td colSpan={3} className="text-end fw-bold">
                    Total
                  </td>
                  <td colSpan={2} className="fw-bold">
                    {totalAmount}
                  </td>
                </tr>
              </tfoot>
            </table>
            <div className="mt-3 text-end">
              <button
                type="button"
                className="btn btn-primary"
                onClick={handleSubmit}
              >
                Add fee / Update fee
              </button>
            </div>
          </div>
        </div>
      </div>
    </>

  );
};

export default ClassFeesMasterForm;


