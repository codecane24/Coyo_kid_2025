import React, { useEffect, useRef, useState } from "react";
import { Link } from "react-router-dom";
import { all_routes } from "../../../router/all_routes";
import PredefinedDateRanges from "../../../../core/common/datePicker";
import CommonSelect from "../../../../core/common/commonSelect";
import TooltipOption from "../../../../core/common/tooltipOption";
import { allClass } from "../../../../core/common/selectoption/selectoption";
import { getInquiryList } from "../../../../services/AdmissionInquiry";

interface AdmissionInquiryGridData {
  id: string | number;
  code: string;
  first_name: string;
  middle_name?: string;
  last_name: string;
  selected_class: string;
  gender: string;
  primary_contact: string;
  status: string | boolean;
  email?: string;
  date_of_enquiry: string;
}

const AdmissionInquiryGrid = () => {
  const routes = all_routes;
  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  const [data, setData] = useState<AdmissionInquiryGridData[]>([]);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await getInquiryList();
        setData(res?.data || []);
      } catch (err) {
        setData([]);
      }
    };
    fetchData();
  }, []);

  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };

  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">
        <div className="content content-two">
          {/* Page Header */}
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Admission Inquiries</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">Peoples</li>
                  <li className="breadcrumb-item active" aria-current="page">
                    Admission Inquiries
                  </li>
                </ol>
              </nav>
            </div>
            <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
              <TooltipOption />
              <div className="mb-2">
                <Link
                  to={routes.addAdmissionInquiry}
                  className="btn btn-primary d-flex align-items-center"
                >
                  <i className="ti ti-square-rounded-plus me-2" />
                  Add Inquiry
                </Link>
              </div>
            </div>
          </div>
          {/* /Page Header */}
          <div className="bg-white p-3 border rounded-1 d-flex align-items-center justify-content-between flex-wrap mb-4 pb-0">
            <h4 className="mb-3">Admission Inquiry Grid</h4>
            <div className="d-flex align-items-center flex-wrap">
              <div className="input-icon-start mb-3 me-2 position-relative">
                <PredefinedDateRanges />
              </div>
              <div className="dropdown mb-3 me-2">
                <Link
                  to="#"
                  className="btn btn-outline-light bg-white dropdown-toggle"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="outside"
                >
                  <i className="ti ti-filter me-2" />
                  Filter
                </Link>
                <div className="dropdown-menu drop-width" ref={dropdownMenuRef}>
                  <form>
                    <div className="d-flex align-items-center border-bottom p-3">
                      <h4>Filter</h4>
                    </div>
                    <div className="p-3 pb-0 border-bottom">
                      <div className="row">
                        <div className="col-md-12">
                          <div className="mb-3">
                            <label className="form-label">Class</label>
                            <CommonSelect
                              className="select"
                              options={allClass}
                              defaultValue={allClass[0]}
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="p-3 d-flex align-items-center justify-content-end">
                      <Link to="#" className="btn btn-light me-3">
                        Reset
                      </Link>
                      <Link
                        to={routes.admissionInquiryGrid}
                        onClick={handleApplyClick}
                        className="btn btn-primary"
                      >
                        Apply
                      </Link>
                    </div>
                  </form>
                </div>
              </div>
              <div className="d-flex align-items-center bg-white border rounded-2 p-1 mb-3 me-2">
                <Link
                  to={routes.admissionInquiryList}
                  className="btn btn-icon btn-sm me-1 bg-light primary-hover"
                >
                  <i className="ti ti-list-tree" />
                </Link>
                <Link
                  to={routes.admissionInquiryGrid}
                  className="active btn btn-icon btn-sm primary-hover"
                >
                  <i className="ti ti-grid-dots" />
                </Link>
              </div>
              <div className="dropdown mb-3">
                <Link
                  to="#"
                  className="btn btn-outline-light bg-white dropdown-toggle"
                  data-bs-toggle="dropdown"
                >
                  <i className="ti ti-sort-ascending-2 me-2" />
                  Sort by A-Z{" "}
                </Link>
                <ul className="dropdown-menu p-3">
                  <li>
                    <Link to="#" className="dropdown-item rounded-1 active">
                      Ascending
                    </Link>
                  </li>
                  <li>
                    <Link to="#" className="dropdown-item rounded-1">
                      Descending
                    </Link>
                  </li>
                  <li>
                    <Link to="#" className="dropdown-item rounded-1">
                      Recently Viewed
                    </Link>
                  </li>
                  <li>
                    <Link to="#" className="dropdown-item rounded-1">
                      Recently Added
                    </Link>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div className="row">
            {data.length === 0 && (
              <div className="col-12 text-center py-5">No inquiries found.</div>
            )}
            {data.map((item) => (
              <div className="col-xxl-3 col-xl-4 col-md-6 d-flex" key={item.id}>
                <div className="card flex-fill">
                  <div className="card-header d-flex align-items-center justify-content-between">
                    <Link
                      to={routes.admissionInquiryDetails.replace(":id", String(item.id))}
                      className="link-primary"
                    >
                      {item.code}
                    </Link>
                    <div className="d-flex align-items-center">
                      <span
                        className={`badge d-inline-flex align-items-center me-1 ${
                          item.status === true || item.status === "Active"
                            ? "badge-soft-success"
                            : "badge-soft-danger"
                        }`}
                      >
                        <i className="ti ti-circle-filled fs-5 me-1" />
                        {item.status === true || item.status === "Active"
                          ? "Active"
                          : "Inactive"}
                      </span>
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
                              to={routes.editAdmissionInquiry.replace(":id", String(item.id))}
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
                  </div>
                  <div className="card-body">
                    <div className="bg-light-300 rounded-2 p-3 mb-3">
                      <div className="d-flex align-items-center">
                        <div className="avatar avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-4 me-3">
                          {item.first_name.charAt(0)}
                        </div>
                        <div className="ms-2">
                          <h6 className="text-dark text-truncate mb-0">
                            <Link to={routes.admissionInquiryDetails.replace(":id", String(item.id))}>
                              {item.first_name} {item.middle_name || ""} {item.last_name}
                            </Link>
                          </h6>
                          <p>{item.selected_class}</p>
                        </div>
                      </div>
                    </div>
                    <div>
                      <div className="mb-2">
                        <p className="mb-0">Date of Inquiry</p>
                        <p className="text-dark">{item.date_of_enquiry}</p>
                      </div>
                      <div className="mb-2">
                        <p className="mb-0">Email</p>
                        <p className="text-dark">{item.email || "-"}</p>
                      </div>
                      <div>
                        <p className="mb-0">Phone</p>
                        <p className="text-dark">{item.primary_contact}</p>
                      </div>
                    </div>
                  </div>
                  <div className="card-footer d-flex align-items-center justify-content-between">
                    <span className="badge badge-soft-danger">{item.gender}</span>
                    <Link
                      to={routes.admissionInquiryDetails.replace(":id", String(item.id))}
                      className="btn btn-light btn-sm"
                    >
                      View Details
                    </Link>
                  </div>
                </div>
              </div>
            ))}
          </div>
          <div className="text-center">
            <Link
              to="#"
              className="btn btn-primary d-inline-flex align-items-center"
            >
              <i className="ti ti-loader-3 me-2" />
              Load More
            </Link>
          </div>
        </div>
      </div>
      {/* /Page Wrapper */}
    </>
  );
};

export default AdmissionInquiryGrid;