import React, { useRef, useState, useEffect } from "react";
import { all_routes } from "../../router/all_routes";
import { Link, useNavigate } from "react-router-dom";
import { getClassWiseFeesList } from "../../../services/FeesAllData";
import TooltipOption from "../../../core/common/tooltipOption";
import FeesMasterModal from "./feesMasterModal";

const ClassFeesMaster = () => {
  const routes = all_routes;
  const [classFeesList, setClassFeesList] = useState<any[]>([]);
  const navigate = useNavigate();

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

  return (
    <>
      <div className="page-wrapper">
        <div className="content">
          {/* Page Header */}
          <div className="page-header">
            <div className="row align-items-center">
              <div className="col">
                <h3 className="page-title">Class Fees Master</h3>
                <ul className="breadcrumb">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">Fees Management</li>
                  <li className="breadcrumb-item active">Class Fees Master</li>
                </ul>
              </div>
              <div className="col-auto float-end ms-auto d-flex gap-2 align-items-center">
                <TooltipOption />
                <Link
                  to="#"
                  className="btn btn-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#add_fees_master"
                >
                  <i className="ti ti-square-rounded-plus me-2" />
                  Add Class Fees
                </Link>
              </div>
            </div>
          </div>
          {/* /Page Header */}
          <div className="card card-table">
            <div className="card-body">
              <div className="table-responsive">
                <table className="table border-0 custom-table mb-0">
                  <thead>
                    <tr>
                      <th>
                        <div className="form-check custom-checkbox checkbox-primary">
                          <input type="checkbox" className="form-check-input" />
                        </div>
                        S.No
                      </th>
                      <th>Class Name</th>
                      <th>Fees Group</th>
                      <th>Fees Type</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th className="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {tableRows.map((row, idx) => (
                      <tr key={row.key}>
                        {row.showClass ? (
                          <>
                            <td rowSpan={row.rowSpan}>
                              <div className="form-check custom-checkbox checkbox-primary d-inline-block me-2">
                                <input type="checkbox" className="form-check-input" />
                              </div>
                              {row.sNo}
                            </td>
                            <td rowSpan={row.rowSpan}>{row.className}</td>
                          </>
                        ) : null}
                        <td>{row.feesGroup}</td>
                        <td>{row.feesType}</td>
                        <td>{row.amount}</td>
                        <td>
                          <span className={`badge ${row.status === "active" ? "badge-soft-success" : "badge-soft-danger"}`}>
                            {row.status.charAt(0).toUpperCase() + row.status.slice(1)}
                          </span>
                        </td>
                        <td className="text-end">
                          <div className="dropdown">
                            <Link
                              to="#"
                              className="btn btn-white btn-sm btn-rounded"
                              data-bs-toggle="dropdown"
                              aria-expanded="false"
                            >
                              <i className="ti ti-dots-vertical fs-5"></i>
                            </Link>
                            <ul className="dropdown-menu dropdown-menu-end">
                              <li>
                                <Link
                                  className="dropdown-item"
                                  to="#"
                                  // onClick={() => handleView(row)}
                                >
                                  <i className="ti ti-eye me-2"></i>
                                  View
                                </Link>
                              </li>
                              <li>
                                <button
                                  className="dropdown-item"
                                  onClick={() => navigate(`/management/edit-class-fees-master/${classFeesList.find(cls => cls.class_name === row.className)?.class_id}`)}
                                >
                                  <i className="ti ti-edit me-2"></i>
                                  Edit
                                </button>
                              </li>
                              <li>
                                <Link
                                  className="dropdown-item"
                                  to="#"
                                  // onClick={() => handleDelete(row)}
                                >
                                  <i className="ti ti-trash me-2"></i>
                                  Delete
                                </Link>
                              </li>
                            </ul>
                          </div>
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
          </div>
        </div>
      </div>
      <FeesMasterModal />
    </>
  );
};

export default ClassFeesMaster;