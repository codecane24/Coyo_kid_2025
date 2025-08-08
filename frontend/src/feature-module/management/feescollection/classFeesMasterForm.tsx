import React, { useRef, useState, useEffect } from "react";
import { all_routes } from "../../router/all_routes";
import { Link, useParams, useNavigate } from "react-router-dom";
import TooltipOption from "../../../core/common/tooltipOption";
import {
  getFeesTypeDropdown,
  getFeesGroupList,
  createClassFeesMaster,
  updateClassFeesMaster,
  getClassWiseFeesList,
} from "../../../services/FeesAllData";
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
  const [isEdit, setIsEdit] = useState(false);

  const feeTypeRef = useRef<HTMLSelectElement>(null);
  const feeAmountRef = useRef<HTMLInputElement>(null);
  const classSelectRef = useRef<any>(null);
  const { id } = useParams(); // id is class_id for edit
  const navigate = useNavigate();

  // Fetch class options
  useEffect(() => {
    getClassesList().then((res: any) => {
      if (res && Array.isArray(res)) {
        setClassOptions(
          res.map((item: any) => ({
            value: String(item.id),
            label: item.name + (item.section ? ` (${item.section})` : ""),
          }))
        );
      }
    });
  }, []);

  // Fetch fees type options
  useEffect(() => {
    getFeesTypeDropdown().then(({ options }) => {
      setFeesTypeOptions(options);
    });
  }, []);

  // Fetch fees group options
  useEffect(() => {
    getFeesGroupList().then((res) => {
      if (res && res.status === "success" && Array.isArray(res.data)) {
        setFeeGroupOptions(
          res.data.map((item: any) => ({
            value: item.id,
            label: item.name,
            groupName: item.feesgroup?.name || "",
          }))
        );
      }
    });
  }, []);

  // Fetch data for edit mode using the `class_id` from the route and prefill the form.
  useEffect(() => {
    if (id) {
      setIsEdit(true);
      getClassWiseFeesList().then((res: any) => {
        if (res && res.status === "success" && Array.isArray(res.data)) {
          const classData = res.data.find(
            (cls: any) => String(cls.class_id) === String(id)
          );
          if (classData) {
            setSelectedClasses([String(classData.class_id)]);
            setFeesList(
              (classData.feestypes || []).map((ft: any, idx: number) => ({
                id: idx + 1,
                type: ft.fees_type_name,
                group: ft.feesgroup_name,
                amount: Number(ft.amount),
              }))
            );
          }
        }
      });
    } else {
      setIsEdit(false);
      setSelectedClasses([]);
      setFeesList([]);
    }
  }, [id]);

  // Show related feestype.feegsroup.name in groupInfo on fees type change
  useEffect(() => {
    if (selectedFeesType && feesTypeOptions.length > 0) {
      const selectedType = feesTypeOptions.find(
        (opt) => String(opt.value) === String(selectedFeesType)
      );
      let groupName = "";
      if (selectedType?.group_id && feeGroupOptions.length > 0) {
        const groupObj = feeGroupOptions.find(
          (g) => String(g.value) === String(selectedType.group_id)
        );
        groupName = groupObj?.label || "";
      }
      setFeesTypeGroupName(groupName);
    } else {
      setFeesTypeGroupName("");
    }
  }, [selectedFeesType, feesTypeOptions, feeGroupOptions]);

  const handleAddFee = () => {
    let hasError = false;

    if (selectedClasses.length === 0) {
      toast.error("Please select at least one class before adding a fee type.");
      hasError = true;
      if (classSelectRef.current) {
        classSelectRef.current.focus();
      }
    }
    if (!selectedFeesType) {
      toast.error("Select fees type");
      hasError = true;
      if (feeTypeRef.current) {
        feeTypeRef.current.style.borderColor = "red";
        feeTypeRef.current.focus();
      }
    }
    if (!feeAmount) {
      toast.error("Enter amount");
      hasError = true;
      if (feeAmountRef.current) {
        feeAmountRef.current.style.borderColor = "red";
        feeAmountRef.current.focus();
      }
    }
    if (hasError) return;

    // Prevent duplicate fee type in the list
    const selectedType = feesTypeOptions.find(
      (opt) => String(opt.value) === String(selectedFeesType)
    );
    let typeName = "";
    let groupName = "";
    if (selectedType) {
      typeName = selectedType.label || selectedType.name || "";
      if (feesList.some((fee) => fee.type === typeName)) {
        toast.error("This fee type is already added.");
        if (feeTypeRef.current) {
          feeTypeRef.current.style.borderColor = "red";
          feeTypeRef.current.focus();
        }
        return;
      }
      if (selectedType.group_id && feeGroupOptions.length > 0) {
        const groupObj = feeGroupOptions.find(
          (g) => String(g.value) === String(selectedType.group_id)
        );
        groupName = groupObj?.label || groupObj?.name || "";
        if (!groupName && groupObj?.groupName) {
          groupName = groupObj.groupName;
        }
      }
    }

    if (feeTypeRef.current) feeTypeRef.current.style.borderColor = "";
    if (feeAmountRef.current) feeAmountRef.current.style.borderColor = "";

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
      feesTypeOptions.find((opt) => opt.label === fee.type)?.value || ""
    );
    setFeeGroupName(fee.group);
    setFeeAmount(String(fee.amount));
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

  const totalAmount = feesList.reduce(
    (sum, fee) => sum + (fee.amount || 0),
    0
  );

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
      feestypes: feesList.map((fee) => ({
        feestype_id:
          feesTypeOptions.find((opt) => opt.label === fee.type)?.value || "",
        amount: fee.amount,
      })),
    };
    try {
      let res;
      if (isEdit && id) {
        // Fix: updateClassFeesMaster expects (payload, id)
        res = await updateClassFeesMaster(id, payload);
      } else {
        res = await createClassFeesMaster(payload);
      }
      if (res && res.status === "success") {
        toast.success(
          isEdit
            ? "Class Fees Master updated successfully!"
            : "Class Fees Master created successfully!"
        );
        setSelectedClasses([]);
        setFeesList([]);
        navigate(all_routes.classFeesMaster);
      } else if (res?.message) {
        toast.error(res.message);
      } else {
        toast.error(
          isEdit
            ? "Failed to update Class Fees Master."
            : "Failed to create Class Fees Master."
        );
      }
    } catch (err: any) {
      toast.error(
        err?.response?.data?.message ||
          err?.message ||
          "API error. Please try again."
      );
    }
  };

  return (
    <>
      <div className="page-wrapper">
        <div className="content">
          <div className="page-header">
            <div className="row align-items-center">
              <div className="col">
                <h3 className="page-title">
                  {isEdit ? "Edit Class Fees Master" : "Add Class Fees Master"}
                </h3>
                <ul className="breadcrumb">
                  <li className="breadcrumb-item">
                    <Link to={all_routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to={all_routes.classFeesMaster}>Class Fees Master</Link>
                  </li>
                  <li className="breadcrumb-item active">
                    {isEdit ? "Edit" : "Add"}
                  </li>
                </ul>
              </div>
              <div className="col-auto float-end ms-auto d-flex gap-2 align-items-center">
                <TooltipOption />
                <Link
                  to={all_routes.classFeesMaster}
                  className="btn btn-secondary"
                >
                  <i className="ti ti-arrow-left me-2" />
                  Back to List
                </Link>
              </div>
            </div>
          </div>
          <div className="card p-4">
            <h4 className="mb-3">
              {isEdit ? "Edit Class Fees Master" : "Add Class Fees Master"}
            </h4>
            <div className="row mb-3">
              <div className="col-md-3">
                <label>Select Class (multiple)</label>
              </div>
              <div className="col-md-6">
                <Select
                  isMulti
                  options={classOptions}
                  value={classOptions.filter((opt) =>
                    selectedClasses.includes(opt.value)
                  )}
                  onChange={(selected) => {
                    setSelectedClasses(
                      Array.isArray(selected)
                        ? selected.map((opt) => opt.value)
                        : []
                    );
                  }}
                  placeholder="Select Class"
                  classNamePrefix="react-select"
                  isSearchable={true}
                  ref={classSelectRef}
                  isDisabled={isEdit} // Prevent changing class in edit mode
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
                  onChange={(e) => {
                    setSelectedFeesType(e.target.value);
                    if (feeTypeRef.current)
                      feeTypeRef.current.style.borderColor = "";
                  }}
                  ref={feeTypeRef}
                >
                  <option value="">Select Fees Type</option>
                  {feesTypeOptions.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label}
                    </option>
                  ))}
                </select>
                <div className="groupInfo">{feesTypeGroupName}</div>
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
                    onChange={(e) => {
                      setFeeAmount(e.target.value);
                      if (feeAmountRef.current)
                        feeAmountRef.current.style.borderColor = "";
                    }}
                    placeholder="Enter Amount"
                    ref={feeAmountRef}
                  />
                </div>
                <div className="col-md-2 mt-4">
                  <button
                    type="button"
                    className="btn btn-success btn-sm"
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
                {isEdit ? "Update" : "Add"} fee
              </button>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default ClassFeesMasterForm;


