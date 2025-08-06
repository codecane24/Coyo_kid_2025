import React, { useRef, useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { all_routes } from "../../../router/all_routes";
import CommonSelect from "../../../../core/common/commonSelect";
import {
  allClass,
  status,
} from "../../../../core/common/selectoption/selectoption";
import PredefinedDateRanges from "../../../../core/common/datePicker";
import Table from "../../../../core/common/dataTable/index";
import { formatDate } from "../../../../utils/dateUtils";
import { getInquiryList } from "../../../../services/AdmissionInquiry";
import TooltipOption from "../../../../core/common/tooltipOption";
import { useRefresh } from "../../../../context/RefreshContext";

// Define the correct type for your admission inquiry data
interface AdmissionInquiryTableData {
  id: string | number;
  code: string;
  first_name: string;
  middle_name?: string;
  last_name: string;
  selected_class: string;
  gender: string;
  primary_contact: string;
  date_of_enquiry: string;
  status: string | boolean;
  // Add any other fields you use in your columns/actions
}

const AdmissionInquiryList = () => {
  const routes = all_routes;
  const [data, setData] = useState<AdmissionInquiryTableData[]>([]);
const { refreshKey } = useRefresh();
  useEffect(() => {
    const fetchInquiryData = async () => {
      try {
        const res = await getInquiryList();
        setData(res?.data || []);
      } catch (err) {
        console.error("Failed to fetch admission inquiries:", err);
        setData([]);
      }
    };
    fetchInquiryData();
  }, []);

  const columns = [
    {
      title: "ID",
      dataIndex: "code",
      render: (text: string, record: AdmissionInquiryTableData) => (
        <Link to={routes.admissionInquiryDetails.replace(":id", String(record.id))} className="link-primary">
          {text}
        </Link>
      ),
      sorter: (a: AdmissionInquiryTableData, b: AdmissionInquiryTableData) => (a.code || "").localeCompare(b.code || ""),
    },
    {
      title: "Name",
      dataIndex: "first_name",
      render: (_: string, record: AdmissionInquiryTableData) => (
        <span>{`${record.first_name} ${record.middle_name || ""} ${record.last_name}`}</span>
      ),
      sorter: (a: AdmissionInquiryTableData, b: AdmissionInquiryTableData) =>
        (`${a.first_name} ${a.middle_name || ""} ${a.last_name}`).localeCompare(
          `${b.first_name} ${b.middle_name || ""} ${b.last_name}`
        ),
    },
    {
      title: "Class",
      dataIndex: "selected_class",
      sorter: (a: AdmissionInquiryTableData, b: AdmissionInquiryTableData) =>
        (a.selected_class || "").localeCompare(b.selected_class || ""),
    },
    {
      title: "Gender",
      dataIndex: "gender",
      sorter: (a: AdmissionInquiryTableData, b: AdmissionInquiryTableData) =>
        (a.gender || "").localeCompare(b.gender || ""),
    },
    {
      title: "Primary Contact",
      dataIndex: "primary_contact",
      sorter: (a: AdmissionInquiryTableData, b: AdmissionInquiryTableData) =>
        (a.primary_contact || "").localeCompare(b.primary_contact || ""),
    },
    {
      title: "Date Of Enquiry",
      dataIndex: "date_of_enquiry",
      render: (dateString: string) => formatDate(dateString),
      sorter: (a: AdmissionInquiryTableData, b: AdmissionInquiryTableData) =>
        (a.date_of_enquiry || "").localeCompare(b.date_of_enquiry || ""),
    },
    {
      title: "Status",
      dataIndex: "status",
      render: (text: string | boolean) => (
        <>
          {text === true || text === "Active" ? (
            <span className="badge badge-soft-success d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              Active
            </span>
          ) : (
            <span className="badge badge-soft-danger d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              Inactive
            </span>
          )}
        </>
      ),
      sorter: (a: AdmissionInquiryTableData, b: AdmissionInquiryTableData) =>
        (a.status || "").toString().localeCompare((b.status || "").toString()),
    },
    {
      title: "Action",
      dataIndex: "action",
      render: (_: any, record: AdmissionInquiryTableData) => (
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
                  to={routes.admissionInquiryDetails.replace(":id", String(record.id))}
                >
                  <i className="ti ti-menu me-2" />
                  View Inquiry
                </Link>
              </li>
              <li>
                <Link
                  className="dropdown-item rounded-1"
                  to={routes.editAdmissionInquiry.replace(":id", String(record.id))}
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
      ),
    },
  ];

  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);

  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };

// const handlePrint = () => {
//     printElementById("print-area"); // ID of the element you want to print
//   };
// const handleExport = (type: "pdf" | "excel") => {
//   const exportColumns = columns
//     .filter(col => col.dataIndex) // skip buttons, render-only columns
//     .map(col => ({
//       title: col.title,
//       field: col.dataIndex as string,
//     }));

//   exportData(type, data, exportColumns, "StudentList");
// };
  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper" key={refreshKey}>
        <div className="content">
          {/* Page Header */}
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Admission Inquiry List</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to="#">Peoples</Link>
                  </li>
                  <li className="breadcrumb-item active" aria-current="page">
                    Admission Inquiry List
                  </li>
                </ol>
              </nav>
            </div>
            <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
               <TooltipOption   showRefresh={false} />
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
          {/* Inquiry List */}
          <div className="card">
            <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
              <h4 className="mb-3">Admission Inquiries</h4>
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
                  <div
                    className="dropdown-menu drop-width "
                    ref={dropdownMenuRef}
                  >
                    <form>
                      <div className="d-flex align-items-center border-bottom p-3">
                        <h4>Filter</h4>
                      </div>
                      <div className="p-3 border-bottom pb-0">
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
                          <div className="col-md-12">
                            <div className="mb-3">
                              <label className="form-label">Status</label>
                              <CommonSelect
                                className="select"
                                options={status}
                                defaultValue={status[0]}
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
                          to="#"
                          className="btn btn-primary"
                          onClick={handleApplyClick}
                        >
                          Apply
                        </Link>
                      </div>
                    </form>
                  </div>
                </div>
                <div className="d-flex align-items-center bg-white border rounded-2 p-1 mb-3 me-2">
                  <Link
                    to="#"
                    className="active btn btn-icon btn-sm me-1 primary-hover"
                  >
                    <i className="ti ti-list-tree" />
                  </Link>
                   <Link
                    to={routes.admissionInquiryGrid}
                    className="btn btn-icon btn-sm me-1 primary-hover"
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
                    Sort by A-Z
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
            <div className="card-body p-0 py-3" >
              {/* Inquiry List Table */}
              <Table dataSource={data} columns={columns} Selection={true} />
              {/* /Inquiry List Table */}
            </div>
          </div>
          {/* /Inquiry List */}
        </div>
      </div>

    </>
  );
};

export default AdmissionInquiryList;
