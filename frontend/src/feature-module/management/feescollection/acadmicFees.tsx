
import React, { useRef } from "react";
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
import { TableData } from "../../../core/data/interface";
import Table from "../../../core/common/dataTable/index";
import FeesModal from "./feesModal";
import { feesMasterData } from "../../../core/data/json/feesMaster";
import TooltipOption from "../../../core/common/tooltipOption";
import FeesModal2 from "./FeesModal2";

const AcadmicFees = () => {
  const routes = all_routes;
  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  const data = feesMasterData;
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
              <h3 className="page-title mb-1">Acadmic Fees</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to="#">Fees Collection</Link>
                  </li>
                  <li className="breadcrumb-item active" aria-current="page">
                  Acadmic Fees
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
                  Add Acadmic Fees
                </Link>
              </div>
            </div>
          </div>
          {/* /Page Header */}
          {/* Students List */}
          <div className="card">
            <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
            
            </div>
      <div className="col-xl-6">
          <div className="card">
            <div className="card-header justify-content-between">
              <div className="card-title">
       Acadim Fees Data
              </div>
            </div>
            <div className="card-body">
              <div className="table-responsive">
                <table className="table text-nowrap">
                  <thead className="table-primary">
                    <tr>
                      <th scope="col">Class</th>
                      <th scope="col">Amount</th>
                      <th scope="col">Last Updated</th>
                      <th scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th scope="row">2</th>
                      <td>50000</td>
                      <td>24 May 2022</td>
                      <td>
                        <div className="hstack gap-2 fs-15">
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-success rounded-pill"><i className="feather-download" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-info rounded-pill"><i className="feather-edit" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-danger rounded-pill"><i className="feather-trash" /></Link>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <th scope="row">Zozo Hadid</th>
                      <td>#5182-3412</td>
                      <td>02 July 2022</td>
                      <td>
                        <div className="hstack gap-2 fs-15">
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-success rounded-pill"><i className="feather-download" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-info rounded-pill"><i className="feather-edit" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-danger rounded-pill"><i className="feather-trash" /></Link>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <th scope="row">Martiana</th>
                      <td>#5182-3423</td>
                      <td>15 April 2022</td>
                      <td>
                        <div className="hstack gap-2 fs-15">
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-success rounded-pill"><i className="feather-download" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-info rounded-pill"><i className="feather-edit" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-danger rounded-pill"><i className="feather-trash" /></Link>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <th scope="row">Alex Carey</th>
                      <td>#5182-3456</td>
                      <td>17 March 2022</td>
                      <td>
                        <div className="hstack gap-2 fs-15">
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-success rounded-pill"><i className="feather-download" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-info rounded-pill"><i className="feather-edit" /></Link>
                          <Link to="#" className="btn btn-icon btn-sm btn-soft-danger rounded-pill"><i className="feather-trash" /></Link>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
          </div>
          {/* /Students List */}
        </div>
      </div>
      {/* /Page Wrapper */}
      <FeesModal2/>
    </>
  );
};

export default  AcadmicFees;
