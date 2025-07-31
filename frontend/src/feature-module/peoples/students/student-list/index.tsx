import React, { useEffect, useRef, useState } from "react";
import { useNavigate } from "react-router-dom";
import { Link } from "react-router-dom";
import { all_routes } from "../../../router/all_routes";
import { Studentlist } from "../../../../core/data/json/studentList";
import { TableData } from "../../../../core/data/interface";
import ImageWithBasePath from "../../../../core/common/imageWithBasePath";
import StudentModals from "../studentModals";
import Table from "../../../../core/common/dataTable/index";
import PredefinedDateRanges from "../../../../core/common/datePicker";
import {
  allClass,
  allSection,
  gender,
  names,
  status,
} from "../../../../core/common/selectoption/selectoption";
import CommonSelect from "../../../../core/common/commonSelect";
import TooltipOption from "../../../../core/common/tooltipOption";
import { getStudent } from "../../../../services/StudentData";
import { exportData } from "../../../../utils/exportHelper";
import { printElementById } from "../../../../utils/printHelper";
const MyComponent = () => {
  const tableData = [
    { name: "John", age: 25, city: "New York" },
    { name: "Priya", age: 30, city: "Ahmedabad" },
  ];

  const columns = [
    { title: "Name", field: "name" },
    { title: "Age", field: "age" },
    { title: "City", field: "city" },
  ];

  const handleExport = (type: "pdf" | "excel") => {
    exportData(type, tableData, columns, "MyTableData");
  };

  return <TooltipOption onExport={handleExport} />;
};

const StudentList = () => {
  const routes = all_routes;
    const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
const [data, setData] = useState<any[]>([]);
const [loading, setLoading] = useState(true);
 const [refreshKey, setRefreshKey] = useState(0);
// Inside your component
const navigate = useNavigate();

useEffect(() => {
  const fetchStudents = async () => {
    try {
      const response = await getStudent();
      const studentData = response?.data || [];
      console.log("✅ API Response:", studentData);
      setData(studentData);
    } catch (error) {
      console.error("❌ Failed to fetch student data:", error);
    } finally {
      setLoading(false);
    }
  };

  fetchStudents();
}, []);

  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };

  
const columns = [
  {
    title: "Admission No",
    dataIndex: "code",
    render: (text: string) => text || "N/A",
  },
  {
    title: "Roll No",
    dataIndex: "role_no", // spelling matches your API
    render: (text: string) => text || "N/A",
  },
  {
    title: "Name",
    render: (_: any, record: any) => {
      const fullName = `${record.first_name || ""} ${record.last_name || ""}`.trim();
      return (
        <div className="d-flex align-items-center">
          <div className="avatar avatar-md">
            <ImageWithBasePath
              src={record.imgSrc || "/default-user.png"}
              className="img-fluid rounded-circle"
              alt="img"
            />
          </div>
          <div className="ms-2">
            <p className="text-dark mb-0">{fullName || "N/A"}</p>
          </div>
        </div>
      );
    },
  },
  {
    title: "Class",
    dataIndex: "class_id",
    render: (text: string) => text || "N/A",
  },
  {
    title: "Section",
    render: () => "N/A", // section not available
  },
  {
    title: "Gender",
    dataIndex: "gender",
    render: (text: string) =>
      text ? text.charAt(0).toUpperCase() + text.slice(1) : "N/A",
  },
  {
    title: "Status",
    dataIndex: "status",
    render: (value: string | number) => {
      const statusText = value === "1" || value === 1 ? "Active" : "Inactive";
      const badgeClass =
        statusText === "Active"
          ? "badge badge-soft-success"
          : "badge badge-soft-danger";
      return (
        <span className={`${badgeClass} d-inline-flex align-items-center`}>
          <i className="ti ti-circle-filled fs-5 me-1" />
          {statusText}
        </span>
      );
    },
  },
  {
    title: "Date of Join",
    dataIndex: "doj",
    render: (text: string) => text || "N/A",
  },
  {
    title: "DOB",
    dataIndex: "dob",
    render: (text: string) => text || "N/A",
  },
// In your column definition:
{
  title: "Actions",
  dataIndex: "actions",
  key: "actions",
  render: (_: unknown, record: any) => (
    <div className="flex gap-2">
   <Link
  className="btn btn-sm btn-primary"
to={`/student/edit-student/${record.encryptid || record.id || record._id}`}

>
  Edit
</Link>
      <button className="btn btn-sm btn-danger">Delete</button>
    </div>
  ),
}
];

// Fuctions to work the tooltip Options
    const handleRefresh = () => {
    setRefreshKey((prev) => prev + 1); // This will re-render component parts
  };
const handlePrint = () => {
    printElementById("print-area"); // ID of the element you want to print
  };
const handleExport = (type: "pdf" | "excel") => {
  const exportColumns = columns
    .filter(col => col.dataIndex) // skip buttons, render-only columns
    .map(col => ({
      title: col.title,
      field: col.dataIndex as string,
    }));

  exportData(type, data, exportColumns, "StudentList");
};

  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper" key={refreshKey}>
        <div className="content">
          {/* Page Header */}
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Students List</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">Students</li>
                  <li className="breadcrumb-item active" aria-current="page">
                    All Students
                  </li>
                </ol>
              </nav>
            </div>
            <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
 <TooltipOption onExport={handleExport} onPrint={handlePrint}  onRefresh={handleRefresh} />

              <div className="mb-2">
                <Link
                  to={routes.addStudent}
                  className="btn btn-primary d-flex align-items-center"
                >
                  <i className="ti ti-square-rounded-plus me-2" />
                  Add Student
                </Link>
              </div>
            </div>
          </div>
          {/* /Page Header */}
          {/* Students List */}
          <div className="card">
            <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
              <h4 className="mb-3">Students List</h4>
              <div className="d-flex align-items-center flex-wrap" >
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
                    className="dropdown-menu drop-width"
                    ref={dropdownMenuRef}
                  >
                    <form>
                      <div className="d-flex align-items-center border-bottom p-3">
                        <h4>Filter</h4>
                      </div>
                      <div className="p-3 pb-0 border-bottom">
                        <div className="row">
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Class</label>
                              <CommonSelect
                                className="select"
                                options={allClass}
                                defaultValue={allClass[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Section</label>
                              <CommonSelect
                                className="select"
                                options={allSection}
                                defaultValue={allSection[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-12">
                            <div className="mb-3">
                              <label className="form-label">Name</label>
                              <CommonSelect
                                className="select"
                                options={names}
                                defaultValue={names[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Gender</label>
                              <CommonSelect
                                className="select"
                                options={gender}
                                defaultValue={gender[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
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
                          to={routes.studentGrid}
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
                    to={routes.studentList}
                    className="active btn btn-icon btn-sm me-1 primary-hover"
                  >
                    <i className="ti ti-list-tree" />
                  </Link>
                  <Link
                    to={routes.studentGrid}
                    className="btn btn-icon btn-sm bg-light primary-hover"
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
          <div className="card-body p-0 py-3 " id="print-area">
      {/* Student List */}
      <Table  dataSource={data} columns={columns} Selection={true}  />
      {/* /Student List */}
    </div>
          </div>
          {/* /Students List */}
        </div>
      </div>
      {/* /Page Wrapper */}
      <StudentModals />
    </>
  );
};

export default StudentList;
