import React, { useRef, useState, useEffect } from "react";
import { all_routes } from "../../router/all_routes";
import { Link } from "react-router-dom";
import PredefinedDateRanges from "../../../core/common/datePicker";
import CommonSelect from "../../../core/common/commonSelect";
import { toast } from "react-toastify";
import { 
  getFeesTypeDropdown,
  getFeesGroupList,
  createClassFeesMaster,
  updateClassFeesMaster,
  getClassFeesMasterList,
  getClassFeesMasterById,
  deleteClassFeesMaster,
  getClassFeesByClassId,
  getClassWiseFeesList
} from "../../../services/FeesAllData";
import {
    DueDate,
  feeGroup,
  feesTypes,
  fineType,
  ids,
  status,
} from "../../../core/common/selectoption/selectoption";
import { TableData } from "../../../core/data/interface";
import Table from "../../../core/common/dataTable/index";

import { feesMasterData } from "../../../core/data/json/feesMaster";
import TooltipOption from "../../../core/common/tooltipOption";
import FeesMasterModal from "./feesMasterModal";

const ClassFeesMaster = () => {
  const routes = all_routes;
  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  const data = feesMasterData;
  const [editType, setEditType] = useState<any>(null);
  const [showEditModal, setShowEditModal] = useState(false);
  const [classFeesList, setClassFeesList] = useState<any[]>([]);

  useEffect(() => {
    getClassWiseFeesList().then((res: any) => {
      if (res && res.status === "success" && Array.isArray(res.data)) {
        setClassFeesList(res.data);
      }
    });
  }, []);

  // Prepare table rows for the required structure
  const tableRows: any[] = [];
  classFeesList.forEach((cls, classIdx) => {
    if (Array.isArray(cls.feestypes) && cls.feestypes.length > 0) {
      cls.feestypes.forEach((ft: any, ftIdx: number) => {
        tableRows.push({
          key: `${cls.class_id}-${ft.feestype_id}`,
          sNo: ftIdx === 0 ? classIdx + 1 : "",
          showClass: ftIdx === 0,
          rowSpan: ftIdx === 0 ? cls.feestypes.length : 0,
          className: cls.class_name,
          feesGroup: ft.feesgroup_name,
          feesType: ft.fees_type_name,
          amount: ft.amount,
          status: "active", // or use ft.status if available
        });
      });
    }
  });

  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };
  const columns = [
    {
      title: "ID",
      dataIndex: "id",
      render: (text: string) => (
        <Link to="#" className="link-primary">
          {text}
        </Link>
      ),
      sorter: (a: TableData, b: TableData) => a.id.length - b.id.length,
    },
    {
        title: "Fees Group",
        dataIndex: "feesGroup",
        sorter: (a: TableData, b: TableData) =>
          a.feesGroup.length - b.feesGroup.length,
      },

    {
      title: "Fees Type",
      dataIndex: "feesType",
      sorter: (a: TableData, b: TableData) =>
        a.feesType.length - b.feesType.length,
    },
    {
        title: "Due Date",
        dataIndex: "dueDate",
        sorter: (a: TableData, b: TableData) =>
          a.dueDate.length - b.dueDate.length,
      },
    {
      title: "Amount ($)",
      dataIndex: "amount",
      sorter: (a: TableData, b: TableData) =>
        a.amount.length - b.amount.length,
    },

    {
      title: "Fine Type",
      dataIndex: "fineType",
      render: (text: string,res:any) => (
        <>
          <span className={res.fineClass}>{text}</span>
        </>
      ),
      sorter: (a: TableData, b: TableData) =>
        a.fineType.length - b.fineType.length,
    },
    {
        title: "Fine Amount ($)",
        dataIndex: "fineAmount",
        sorter: (a: TableData, b: TableData) =>
          a.fineAmount.length - b.fineAmount.length,
      },
  
    {
      title: "Status",
      dataIndex: "status",
      render: (text: string) => (
        <>
          {text === "Active" ? (
            <span className="badge badge-soft-success d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              {text}
            </span>
          ) : (
            <span className="badge badge-soft-danger d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              {text}
            </span>
          )}
        </>
      ),
      sorter: (a: TableData, b: TableData) => a.status.length - b.status.length,
    },
    {
      title: "Action",
      dataIndex: "action",
      render: () => (
        <>
          <div className="d-flex align-items-center">
            <div className="dropdown">
              <Link
                to="#"
                className="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
                data-bs-toggle="dropdown"
                aria-expanded="false"
              >
                <i className="ti ti-dots-vertical fs-14" />
              </Link>
              <ul className="dropdown-menu dropdown-menu-right p-3">
                <li>
                  <Link
                    className="dropdown-item rounded-1"
                    to="#"
                    data-bs-toggle="modal"
                    data-bs-target="#edit_fees_master"
                  >
                    <i className="ti ti-edit-circle me-2" />
                    Edit
                  </Link>
                </li>
                <li>
                  <Link
                    className="dropdown-item rounded-1"
                    to="#"
                    data-bs-toggle="modal"
                    data-bs-target="#delete-modal"
                  >
                    <i className="ti ti-trash-x me-2" />
                    Delete
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </>
      ),
    },
  ];
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
          {/* Students List */}
          <div className="card">
            <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
              <h4 className="mb-3">Class Fees Master List</h4>
            </div>
            <div className="card-body p-0 py-3">
              <table className="table table-bordered">
                <thead>
                  <tr>
                    <th>check box/S.no</th>
                    <th>Class Name</th>
                    <th>Fees Group</th>
                    <th>Fees Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action (view,Edit,delete)</th>
                  </tr>
                </thead>
                <tbody>
                  {tableRows.map((row, idx) => (
                    <tr key={row.key}>
                      {row.showClass ? (
                        <>
                          <td rowSpan={row.rowSpan}>
                            <input type="checkbox" /> {row.sNo}
                          </td>
                          <td rowSpan={row.rowSpan}>{row.className}</td>
                        </>
                      ) : null}
                      <td>{row.feesGroup}</td>
                      <td>{row.feesType}</td>
                      <td>{row.amount}</td>
                      <td>{row.status}</td>
                      <td>
                        <button className="btn btn-sm btn-light">View</button>
                        <button className="btn btn-sm btn-primary ms-1">Edit</button>
                        <button className="btn btn-sm btn-danger ms-1">Delete</button>
                      </td>
                    </tr>
                  ))}
                  {tableRows.length === 0 && (
                    <tr>
                      <td colSpan={7} className="text-center">
                        No data found.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
          {/* /Students List */}
        </div>
      </div>
      {/* /Page Wrapper */}
      <FeesMasterModal/>
    </>
  );
};

export default ClassFeesMaster;
