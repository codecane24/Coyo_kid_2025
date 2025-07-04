import React, { useRef, useState, useEffect, ChangeEvent, FormEvent  } from "react";
import { classes } from "../../../core/data/json/classes";
import Table from "../../../core/common/dataTable/index";
import PredefinedDateRanges from "../../../core/common/datePicker";
import {
  activeList,
  classSection,
  classSylabus,
} from "../../../core/common/selectoption/selectoption";
import CommonSelect from "../../../core/common/commonSelect";
// import { TableData } from "../../../core/data/interface";
import { Link } from "react-router-dom";
import TooltipOption from "../../../core/common/tooltipOption";
import { all_routes } from "../../router/all_routes";
import { getClassesList } from "../../../services/ClassData";
import { getClassMaster } from "../../../services/ClassData";
import { getSection } from "../../../services/ClassData";
import { createClass } from "../../../services/ClassData";
type Section = {
  id: string;
  name: string;
  // Add more fields if needed
};

type ClassItem = {
  id: string;
  name: string;
  masterclass_id: string;
  class_code: string;
  room_number: string;
  section_id: string;
  status: string;
  created_at: string;
  updated_at: string;
  section: Section; // Nested section
};

// At the top of your component file (or separately)
type OptionType = {
  label: string;
  value: string;
};

type FormDataType = {
  classMaster: OptionType;
  className: string;
  section: OptionType;
  roomNo: string;
  status: OptionType;
};

type ErrorType = {
  [key in keyof FormDataType]?: string;
};
interface TableData {
  key: number;
  id: string;
  class: string;
  section: string;
  noOfStudents: number;
  noOfSubjects: number;
  status: string;
}

interface ClassTableRow {
  key: number;
  id: string;
  class: string;
  section: string;
  roomNo: string;
  noOfStudents: number | string; // number or 'N/A'
  noOfSubjects: number;
  status: string;
}

const Classes = () => {
  const routes = all_routes;
const [allClassData, setAllClassData] = useState<ClassItem[]>([]);
const [classOptions, setClassOptions] = useState<{ label: string; value: string }[]>([]);
const [selectedClassId, setSelectedClassId] = useState<string | null>(null);
const [classSection, setClassSection] = useState<{ label: string; value: string }[]>([]);
const [classMasterOptions, setClassMasterOptions] = useState<{ label: string; value: string }[]>([]);
const [Section, setSection] = useState<{ label: string; value: string }[]>([]);
useEffect(() => {
  const fetchClassMasterList = async () => {
    try {
      const res = await getClassMaster(); // 👈 this API returns master class list

      const formatted = res.map((cls: any) => ({
        label: cls.name,
        value: cls.id.toString(),
      }));

      setClassMasterOptions(formatted);
    } catch (error) {
      console.error("Failed to fetch class master list:", error);
    }
  };

  fetchClassMasterList();
}, []);

useEffect(() => {
  const fetchMasterClasses = async () => {
    try {
      const res = await getClassesList(); // API you already used
      const formatted = res.map((cls: any) => ({
        label: cls.name,
        value: cls.id.toString(), // or keep it number if you prefer
      }));
      setClassSection(formatted);
    } catch (err) {
      console.error("Failed to fetch master classes:", err);
    }
  };

  fetchMasterClasses();
}, []);
useEffect(() => {
  const fetchSection= async () => {
    try {
      const res = await getSection(); // 👈 this API returns master class list

      const formatted = res.map((section: any) => ({
        label: section.name,
        value: section.id.toString(),
      }));

      setSection(formatted);
    } catch (error) {
      console.error("Failed to fetch class master list:", error);
    }
  };

  fetchSection();
}, []);
const [tableData, setTableData] = useState<ClassTableRow[]>([]);


useEffect(() => {
  const fetchTableData = async () => {
    try {
      const res = await getClassesList();

      const formatted = res.map((item: any, i: number) => ({
        key: i + 1,
        id: `C${1000 + i}`, // or item.class_code if it exists
        class: item.name || "N/A",         // ✅ from `name`
        section: item.section || "N/A",
        roomNo: item.room_no || "N/A",
        noOfStudents: item.noOfStudents ?? "N/A", // optional field for future
        noOfSubjects: item.noOfSubjects ?? 0,
        status: item.status === "1" ? "Active" : "Inactive",
      }));

      setTableData(formatted);
    } catch (err) {
      console.error("Failed to fetch data", err);
    }
  };

  fetchTableData();
}, []);


const selectedClass = allClassData.find(cls => cls.id === selectedClassId);
  const data = classes;
  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  
  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };
  const route = all_routes
  const columns = [
  {
    title: "ID",
    dataIndex: "id",
    render: (text: string, record: any) => (
      <Link to="#" className="link-primary">
        {record.id ?? "N/A"}
      </Link>
    ),
  },
  {
    title: "Class",
    dataIndex: "class",
    sorter: (a: any, b: any) => (a.class ?? "").localeCompare(b.class ?? ""),
    render: (value: string) => value ?? "N/A",
  },
  {
    title: "Section",
    dataIndex: "section",
    sorter: (a: any, b: any) =>
      (a.section ?? "").localeCompare(b.section ?? ""),
    render: (value: string) => value ?? "N/A",
  },
  {
    title: "No of Student",
    dataIndex: "noOfStudents",
    sorter: (a: any, b: any) =>
      (a.noOfStudents ?? 0) - (b.noOfStudents ?? 0),
    render: (value: number) => value ?? "N/A",
  },
 {
  title: "Class Room No",
  dataIndex: "roomNo", // ✅ use the correct field
  sorter: (a: any, b: any) =>
    (a.roomNo ?? "").localeCompare(b.roomNo ?? ""),
  render: (value: string) => value ?? "N/A",
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
  },
  {
    title: "Action",
    dataIndex: "action",
    render: () => (
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
                data-bs-target="#edit_class"
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

  const statusOptions = [
  { label: "Active", value: "1" },
  { label: "Inactive", value: "0" },
];
  // Form validation and post request logic for add form
   const [formData, setFormData] = useState<FormDataType>({
    classMaster: classMasterOptions[0],
    className: "",
    section: Section[0],
    roomNo: "",
    status: statusOptions[0],
  });

  const [errors, setErrors] = useState<ErrorType>({});

  const handleInputChange = (e: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSelectChange = (name: keyof FormDataType, selectedOption: OptionType) => {
    setFormData((prev) => ({
      ...prev,
      [name]: selectedOption,
    }));
  };

  const validateForm = () => {
    const newErrors: ErrorType = {};
    // if (!formData.className.trim()) newErrors.className = "Class name is required.";
    if (!formData.roomNo.trim()) newErrors.roomNo = "Room number is required.";
    if (!formData.classMaster) newErrors.classMaster = "Class master is required.";
    if (!formData.section) newErrors.section = "Section is required.";
    if (!formData.status) newErrors.status = "Status is required.";
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

 const handleSubmit = async (e: FormEvent) => {
  e.preventDefault();
  if (!validateForm()) return;

  const payload = {
    classmaster_id: formData.classMaster.value,
    name: formData.className,
    section: formData.section.value,
    room_no: formData.roomNo,
    status: formData.status.value,
  };

  try {
    const response = await createClass(payload); // 👈 using imported API
    console.log(response)
    if (response.status ) {
      alert("Class added successfully!");
      // Reset form if needed
    } else {
      alert("Failed to add class.");
    }
  } catch (error: any) {
    console.error("Error:", error);
    alert("Something went wrong. Please try again.");
  }
  console.log(payload)
};
// console.log(getClassesList)
  return (
    <div>
      {/* Page Wrapper */}
      <div className="page-wrapper">
        <div className="content">
          {/* Page Header */}
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Classes List</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={route.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to="#">Classes </Link>
                  </li>
                  <li className="breadcrumb-item active" aria-current="page">
                    All Classes
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
                  data-bs-target="#add_class"
                >
                  <i className="ti ti-square-rounded-plus-filled me-2" />
                  Add Class
                </Link>
              </div>
            </div>
          </div>
          {/* /Page Header */}
          {/* Guardians List */}
          <div className="card">
            <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
              <h4 className="mb-3">Classes List</h4>
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
                  <div className="dropdown-menu drop-width"  ref={dropdownMenuRef}>
                    <form >
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
                                options={classSylabus}
                                defaultValue={classSylabus[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-12">
                            <div className="mb-3">
                              <label className="form-label">Section</label>
                          <CommonSelect
  className="select"
  options={classSection}
  defaultValue={classSection[0]}
/>

                            </div>
                          </div>
                          <div className="col-md-12">
                            <div className="mb-3">
                              <label className="form-label">Status</label>
                              <CommonSelect
                                className="select"
                                options={activeList}
                                defaultValue={activeList[0]}
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
            <div className="card-body p-0 py-3">
            
    {tableData.length === 0 ? (
  <p className="text-center text-gray-500">No class data comming.</p>
) : (
  <Table columns={columns} dataSource={tableData} />
)}

              {/* /Guardians List */}
            </div>
          </div>
          {/* /Guardians List */}
        </div>
      </div>
      ;{/* /Page Wrapper */}
      <>
        {/* Add Classes */}
    <div className="modal fade" id="add_class">
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content">
          <form onSubmit={handleSubmit}>
            <div className="modal-header">
              <h4 className="modal-title">Add Class</h4>
              <button type="button" className="btn-close custom-btn-close" data-bs-dismiss="modal">
                <i className="ti ti-x" />
              </button>
            </div>
            <div className="modal-body">
              <div className="row">
                <div className="col-md-12">
                  {/* Class Master */}
                  <div className="mb-3">
                    <label className="form-label">Class Master</label>
                    <CommonSelect
                      className="select"
                      options={classMasterOptions}
                      defaultValue={formData.classMaster}
                      onChange={(val: OptionType) => handleSelectChange("classMaster", val)}
                    />
                    {errors.classMaster && <p className="text-danger">{errors.classMaster}</p>}
                  </div>

                  {/* Class Name */}
                  <div className="mb-3">
                    <label className="form-label">Class Name (Optional)</label>
                    <input
                      type="text"
                      name="className"
                      className="form-control"
                      value={formData.className}
                      onChange={handleInputChange}
                    />
    
                  </div>

                  {/* Section */}
                  <div className="mb-3">
                    <label className="form-label">Section</label>
                    <CommonSelect
                      className="select"
                      options={Section}
                      defaultValue={formData.section}
                      onChange={(val: OptionType) => handleSelectChange("section", val)}
                    />
                    {errors.section && <p className="text-danger">{errors.section}</p>}
                  </div>

                  {/* Room No */}
                  <div className="mb-3">
                    <label className="form-label">Class Room No.</label>
                    <input
                      type="text"
                      name="roomNo"
                      className="form-control"
                      value={formData.roomNo}
                      onChange={handleInputChange}
                    />
                    {errors.roomNo && <p className="text-danger">{errors.roomNo}</p>}
                  </div>

                  {/* Active-Inactive */}
                  <div className="mb-3">
                    <label className="form-label">Active-Inactive</label>
                    <CommonSelect
                      className="select"
                      options={statusOptions}
                      defaultValue={formData.status}
                      onChange={(val: OptionType) => handleSelectChange("status", val)}
                    />
                    {errors.status && <p className="text-danger">{errors.status}</p>}
                  </div>
                </div>
              </div>
            </div>

          <div className="modal-footer">
  <Link to="#" className="btn btn-light me-2" data-bs-dismiss="modal">
    Cancel
  </Link>

  {/* ✅ REMOVE data-bs-dismiss here */}
  <button type="submit" className="btn btn-primary">
    Add Class
  </button>
</div>

          </form>
        </div>
      </div>
    </div>
        {/* /Add Classes */}
        {/* Edit Classes */}
        <div className="modal fade" id="edit_class">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">Edit Class</h4>
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
                <div className="modal-body">
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Class Name</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="Enter Class Name"
                          defaultValue="I"
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Section</label>
                        <CommonSelect
                          className="select"
                          options={classSection}
                          defaultValue={classSection[0]}
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">No of Students</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="Enter no of Students"
                          defaultValue={30}
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">No of Subjects</label>
                        <input
                          type="text"
                          className="form-control"
                          placeholder="Enter no of Subjects"
                          // defaultValue={03}
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
                  <Link to="#"  className="btn btn-primary" data-bs-dismiss="modal">
                    Save Changes
                  </Link>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* /Edit Classes */}
        {/* Delete Modal */}
        <div className="modal fade" id="delete-modal">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <form >
                <div className="modal-body text-center">
                  <span className="delete-icon">
                    <i className="ti ti-trash-x" />
                  </span>
                  <h4>Confirm Deletion</h4>
                  <p>
                    You want to delete all the marked items, this cant be undone
                    once you delete.
                  </p>
                  <div className="d-flex justify-content-center">
                    <Link
                      to="#"
                      className="btn btn-light me-3"
                      data-bs-dismiss="modal"
                    >
                      Cancel
                    </Link>
                    <Link to="#" className="btn btn-danger" data-bs-dismiss="modal"
                    >
                      Yes, Delete
                    </Link>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* /Delete Modal */}
        {/* View Classes */}
        <div className="modal fade" id="view_class">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <div className="d-flex align-items-center">
                  <h4 className="modal-title">Class Details</h4>
                  <span className="badge badge-soft-success ms-2">
                    <i className="ti ti-circle-filled me-1 fs-5" />
                    Active
                  </span>
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
                <div className="modal-body">
                  <div className="row">
                    <div className="col-md-6">
                      <div className="class-detail-info">
                        <p>Class Name</p>
                        <span>III</span>
                      </div>
                    </div>
                    <div className="col-md-6">
                      <div className="class-detail-info">
                        <p>Section</p>
                        <span>A</span>
                      </div>
                    </div>
                    <div className="col-md-6">
                      <div className="class-detail-info">
                        <p>No of Subjects</p>
                        <span>05</span>
                      </div>
                    </div>
                    <div className="col-md-6">
                      <div className="class-detail-info">
                        <p>No of Students</p>
                        <span>25</span>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* /View Classes */}
      </>
    </div>
  );
};

export default Classes;
