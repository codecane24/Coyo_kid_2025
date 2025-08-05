import React, { useEffect, useState } from "react";
import { toast } from "react-toastify";
import { createTeacher, updateTeacher, getTeacherById } from "../../../../services/TeacherServices";
import { getAllId } from "../../../../services/GetAllId";
import { Link } from "react-router-dom";
// import { feeGroup, feesTypes, paymentType } from '../../../core/common/selectoption/selectoption'
import { DatePicker } from "antd";
import dayjs from "dayjs";
import { all_routes } from "../../../router/all_routes";
import {
 
  Contract,
  Hostel,
  Marital,
  PickupPoint,
  Shift,
  VehicleNumber,
  allClass,
  allSubject,
  bloodGroup,
  gender,
  roomNO,
  route,
  status,
  getSubject,
} from "../../../../core/common/selectoption/selectoption";
import { TagsInput } from "react-tag-input-component";
import CommonSelect from "../../../../core/common/commonSelect";
import CommonSelectMulti from "../../../../core/common/commonSelectMulti";
import { useLocation, useParams } from "react-router-dom";
import { requiredFields, getMissingFields } from "./teacherFormValidation";
import { getClassesList } from "../../../../services/ClassData";
import { Breadcrumb } from "react-bootstrap";



const TeacherForm = () => {
  const routes = all_routes;
  const { id } = useParams();
  const [isEdit, setIsEdit] = useState<boolean>(!!id);
  const [teacherId, setTeacherId] = useState<string>("");
  const [owner, setOwner] = useState<string[]>([]);
  const [owner1, setOwner1] = useState<string[]>([]);
  const [owner2, setOwner2] = useState<string[]>([]);
  const [defaultDate, setDefaultDate] = useState<dayjs.Dayjs | null>(null);
  const [formData, setFormData] = useState<any>({});
  const [fieldErrors, setFieldErrors] = useState<string[]>([]);
  const [loading, setLoading] = useState(false);
  const [classOptions, setClassOptions] = useState<any[]>([]);
  const [subjectOptions, setSubjectOptions] = useState<any[]>([]);
  // File upload states
  const [resumeFile, setResumeFile] = useState<File | null>(null);
  const [joiningLetterFile, setJoiningLetterFile] = useState<File | null>(null);
  const location = useLocation();

  // Helper to map API data to form fields (for edit mode)
  const mapApiDataToForm = (data: any, classOptions: any[], subjectOptions: any[]) => {
  const findOption = (options: any[], value: any) => options.find(opt => String(opt.value) === String(value)) || value || "";
  return {
    ...data,
    id: data.id || data.code || "",
    first_name: data.first_name || "",
    last_name: data.last_name || "",
    class_id: data.class_id ? findOption(classOptions, data.class_id) : "",
    subject: data.subject_id ? [findOption(subjectOptions, data.subject_id)] : [],
    gender: data.gender ? findOption(gender, data.gender) : "",
    phone: data.phone || "",
    email: data.email || "",
    blood_group: data.blood_group ? findOption(bloodGroup, data.blood_group) : "",
    date_of_joining: data.date_of_joining ? dayjs(data.date_of_joining) : null,
    father_name: data.father_name || "",
    mother_name: data.mother_name || "",
    marital_status: data.marital_status ? findOption(Marital, data.marital_status) : "",
    dob: data.dob ? dayjs(data.dob) : null,
    qualification: data.qualification || "",
    work_experience: data.work_experience || "",
    previous_school: data.previous_school || "",
    previous_school_address: data.previous_school_address || "",
    previous_school_phone: data.previous_school_phone || "",
    address: data.address || "",
    permanent_address: data.permanent_address || "",
    pan_number: data.pan_number || "",
    status: typeof data.status === 'boolean' ? (data.status ? findOption(status, 'Active') : findOption(status, 'Inactive')) : (data.status ? findOption(status, data.status) : ""),
    notes: data.notes || "",
    epf_no: data.epf_no || "",
    basic_salary: data.basic_salary || "",
    contract_type: data.contract_type ? findOption(Contract, data.contract_type) : "",
    work_shift: data.work_shift ? findOption(Shift, data.work_shift) : "",
    work_location: data.work_location || "",
    date_of_leaving: data.date_of_leaving ? dayjs(data.date_of_leaving) : null,
    medical_leaves: data.medical_leaves || "",
    casual_leaves: data.casual_leaves || "",
    maternity_leaves: data.maternity_leaves || "",
    sick_leaves: data.sick_leaves || "",
    account_name: data.account_name || "",
    account_number: data.account_number || "",
    bank_name: data.bank_name || "",
    ifsc_code: data.ifsc_code || "",
    branch_name: data.branch_name || "",
    route: data.route ? findOption(route, data.route) : "",
    vehicle_number: data.vehicle_number ? findOption(VehicleNumber, data.vehicle_number) : "",
    pickup_point: data.pickup_point ? findOption(PickupPoint, data.pickup_point) : "",
    hostel: data.hostel ? findOption(Hostel, data.hostel) : "",
    room_no: data.room_no ? findOption(roomNO, data.room_no) : "",
    facebook: data.facebook || "",
    instagram: data.instagram || "",
    linkedin: data.linkedin || "",
    youtube: data.youtube || "",
    twitter: data.twitter || "",
    // Map API 'languages_known' to form 'language_known'
    language_known: Array.isArray(data.languages_known) ? data.languages_known : (data.languages_known ? [data.languages_known] : []),
  };
};

// Fetch teacher data and map to form fields after options are loaded
useEffect(() => {
  if (id && classOptions.length > 0 && subjectOptions.length > 0) {
    setIsEdit(true);
    (async () => {
      try {
        const teacherData = await getTeacherById(id);
        if (teacherData && teacherData.data) {
          const mapped = mapApiDataToForm(teacherData.data, classOptions, subjectOptions);
          setFormData(mapped);
          setTeacherId(teacherData.data.id || id);
        }
      } catch (err) {
        // Optionally show error
      }
    })();
  } else if (!id) {
    setIsEdit(false);
  }
}, [id, classOptions, subjectOptions]);


useEffect(() => {
  // Fetch new Teacher ID if not editing
  if (!isEdit) {
    getAllId("teacher").then(id => {
      setTeacherId(id);
      setFormData((prev: any) => ({ ...prev, id }));
    });
  } else if (formData.code || formData.id) {
    setTeacherId(formData.code || formData.id);
  }
}, [isEdit, formData.code, formData.id]);



// Classes List With Section Fetching
  useEffect(() => {
    async function fetchClasses() {
      try {
        const classes = await getClassesList();
        const formatted = Array.isArray(classes)
          ? classes.map((cls: any) => ({
            label: `${cls.name} (${cls.section})`,
            value: cls.id
          }))
          : [];
        setClassOptions(formatted);
      } catch (error) {
        toast.error("Failed to load classes");
      }
    }
    fetchClasses();
  }, []);

  useEffect(() => {
    async function fetchSubjects() {
      try {
        const subjects = await getSubject();
        setSubjectOptions(Array.isArray(subjects) ? subjects : []);
      } catch (error) {
        toast.error("Failed to load subjects");
      }
    }
    fetchSubjects();
  }, []);

  // Handle input change for all fields
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev: any) => ({ ...prev, [name]: value }));
    // Remove error indication for this field if corrected
    if (fieldErrors.includes(name)) {
      if (value && value.toString().trim() !== "") {
        setFieldErrors(prev => prev.filter(f => f !== name));
      }
    }
  };

  // Handle select change for CommonSelect
  const handleSelectChange = (name: string, value: any) => {
    setFormData((prev: any) => ({ ...prev, [name]: value }));
    // Remove error indication for this field if corrected
    if (fieldErrors.includes(name)) {
      if (value && value.toString().trim() !== "") {
        setFieldErrors(prev => prev.filter(f => f !== name));
      }
    }
  };

  // Handle date change
  const handleDateChange = (name: string, value: any) => {
    setFormData((prev: any) => ({ ...prev, [name]: value }));
    // Remove error indication for this field if corrected
    if (fieldErrors.includes(name)) {
      if (value && value.toString().trim() !== "") {
        setFieldErrors(prev => prev.filter(f => f !== name));
      }
    }
  };

  // Submit handler
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    // Use centralized validation
    const missingFields = getMissingFields(formData);
    if (missingFields.length > 0) {
      setFieldErrors(missingFields);
      toast.error(`Please fill all compulsory fields: ${missingFields.join(", ")}`);
      // Focus the first error field
      setTimeout(() => {
        const firstError = missingFields[0];
        // Try input, textarea, and select
        const el = document.querySelector(`[name='${firstError}']`);
        if (el && typeof (el as HTMLElement).focus === "function") {
          (el as HTMLElement).focus();
        }
      }, 100);
      return;
    } else {
      setFieldErrors([]);
    }
    setLoading(true);
    // List all fields to submit (add any missing fields here)
    const allFields = [
      "id", "first_name", "last_name", "class", "subject", "gender", "phone", "email", "blood_group", "date_of_joining", "father_name", "mother_name", "marital_status", "dob", "qualification", "work_experience", "previous_school", "previous_school_address", "previous_school_phone", "address", "permanent_address", "pan_number", "status", "notes", "epf_no", "basic_salary", "contract_type", "work_shift", "work_location", "date_of_leaving", "medical_leaves", "casual_leaves", "maternity_leaves", "sick_leaves", "account_name", "account_number", "bank_name", "ifsc_code", "branch_name", "route", "vehicle_number", "pickup_point", "hostel", "room_no", "facebook", "instagram", "linkedin", "youtube", "twitter", "language_known"
    ];

    // Helper: flatten select fields to value
    const flattenValue = (val: any) => {
      if (val && typeof val === "object" && "value" in val) return val.value;
      return val;
    };

    // Transform data for all fields
    const transformedData: any = {};
    allFields.forEach(field => {
      let value = formData[field];
      // Multi-select fields
      if (field === "subject" && Array.isArray(value)) {
        transformedData[field] = value.map((opt: any) => flattenValue(opt));
      } else if (field === "language_known" && Array.isArray(value)) {
        transformedData[field] = value;
      } else if (["class", "gender", "blood_group", "status", "marital_status", "contract_type", "work_shift", "route", "vehicle_number", "pickup_point", "hostel", "room_no"].includes(field)) {
        transformedData[field] = flattenValue(value) || "";
      } else {
        transformedData[field] = value !== undefined && value !== null ? value : "";
      }
    });

    // Prepare FormData for file upload
    const formPayload = new FormData();
    Object.entries(transformedData).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        value.forEach((v, idx) => {
          formPayload.append(`${key}[${idx}]`, typeof v === 'object' ? JSON.stringify(v) : String(v));
        });
      } else if (value instanceof Blob) {
        formPayload.append(key, value);
      } else {
        formPayload.append(key, String(value));
      }
    });
    // Append files if present
    if (resumeFile) {
      formPayload.append("resume", resumeFile);
    }
    if (joiningLetterFile) {
      formPayload.append("joining_letter", joiningLetterFile);
    }
    try {
      if (isEdit) {
        const res = await updateTeacher(transformedData.id, formPayload);
        toast.success("Teacher updated successfully");
      } else {
        const res = await createTeacher(formPayload);
        console.log("Submitting form data:", transformedData);
        toast.success("Teacher added successfully");
      }
    } catch (error: any) {
      const errorMsg = error?.response?.data?.message || error?.message || "Failed to submit teacher data";
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">
        <div className="content content-two">
          {/* Page Header */}
          <div className="page-header d-flex align-items-center justify-content-between mb-4">
  <div>
    <h2 className="mb-1">
      {isEdit ? "Edit Teacher" : "Add Teacher"}
      <span className="badge bg-primary ms-3">
        Teacher Code: {formData.code || teacherId || "N/A"}
      </span>
    </h2>
    <nav>
      <ol className="breadcrumb mb-0">
        <li className="breadcrumb-item">
          <Link to={routes.adminDashboard}>Dashboard</Link>
        </li>
        <li className="breadcrumb-item">
          <Link to={routes.teacherList}>Teacher</Link>
        </li>
        <li className="breadcrumb-item active" aria-current="page">
          {isEdit ? "Edit" : "Add"} Teacher
        </li>
      </ol>
    </nav>
  </div>
</div>
          {/* /Page Header */}
          <div className="row">
            <div className="col-md-12">
              <form onSubmit={handleSubmit}>
                
                <>
                  {/* Personal Information */}
                  <div className="card">
                    <div className="card-header bg-light">
                      <div className="d-flex align-items-center">
                        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                          <i className="ti ti-info-square-rounded fs-16" />
                        </span>
                        <h4 className="text-dark">Personal Information</h4>
                      </div>
                    </div>
                    <div className="card-body pb-1">
                      <div className="row">
                        <div className="col-md-12">
                <div className="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                  <div className="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                    <i className="ti ti-photo-plus fs-16" />
                  </div>
                  <div className="profile-upload">
                    <div className="profile-uploader d-flex align-items-center">
                      <div className="drag-upload-btn mb-3">
                        Upload Resume
                        <input
                          type="file"
                          className="form-control image-sign"
                          accept=".pdf,.jpg,.jpeg,.png"
                          onChange={e => {
                            if (e.target.files && e.target.files[0]) {
                              setResumeFile(e.target.files[0]);
                            }
                          }}
                        />
                        {/* Preview for selected resume file */}
                        {resumeFile && (
                          <div className="mt-2">
                            <span className="fs-12 text-success">Resume: {resumeFile.name}</span>
                            {resumeFile.type.startsWith('image/') ? (
                              <img src={URL.createObjectURL(resumeFile)} alt="Resume Preview" style={{maxWidth:100, maxHeight:100, display:'block', marginTop:4}} />
                            ) : (
                              <span className="fs-12">(PDF or other file)</span>
                            )}
                          </div>
                        )}
                        {/* Preview for edit mode: existing resume file */}
                        {!resumeFile && isEdit && formData.resume_url && (
                          <div className="mt-2">
                            <span className="fs-12 text-info">Current Resume:</span>
                            {formData.resume_url.match(/\.(jpg|jpeg|png)$/i) ? (
                              <img src={formData.resume_url} alt="Resume Preview" style={{maxWidth:100, maxHeight:100, display:'block', marginTop:4}} />
                            ) : (
                              <a href={formData.resume_url} target="_blank" rel="noopener noreferrer">View Resume</a>
                            )}
                          </div>
                        )}
                      </div>
                      <div className="drag-upload-btn mb-3 ms-2">
                        Upload Joining Letter
                        <input
                          type="file"
                          className="form-control image-sign"
                          accept=".pdf,.jpg,.jpeg,.png"
                          onChange={e => {
                            if (e.target.files && e.target.files[0]) {
                              setJoiningLetterFile(e.target.files[0]);
                            }
                          }}
                        />
                        {/* Preview for selected joining letter file */}
                        {joiningLetterFile && (
                          <div className="mt-2">
                            <span className="fs-12 text-success">Joining Letter: {joiningLetterFile.name}</span>
                            {joiningLetterFile.type.startsWith('image/') ? (
                              <img src={URL.createObjectURL(joiningLetterFile)} alt="Joining Letter Preview" style={{maxWidth:100, maxHeight:100, display:'block', marginTop:4}} />
                            ) : (
                              <span className="fs-12">(PDF or other file)</span>
                            )}
                          </div>
                        )}
                        {/* Preview for edit mode: existing joining letter file */}
                        {!joiningLetterFile && isEdit && formData.joining_letter_url && (
                          <div className="mt-2">
                            <span className="fs-12 text-info">Current Joining Letter:</span>
                            {formData.joining_letter_url.match(/\.(jpg|jpeg|png)$/i) ? (
                              <img src={formData.joining_letter_url} alt="Joining Letter Preview" style={{maxWidth:100, maxHeight:100, display:'block', marginTop:4}} />
                            ) : (
                              <a href={formData.joining_letter_url} target="_blank" rel="noopener noreferrer">View Joining Letter</a>
                            )}
                          </div>
                        )}
                      </div>
                      <Link
                        to="#"
                        className="btn btn-primary mb-3 ms-2"
                        onClick={e => {
                          e.preventDefault();
                          setResumeFile(null);
                          setJoiningLetterFile(null);
                        }}
                      >
                        Remove All
                      </Link>
                    </div>
                    <p className="fs-12">
                      Upload image size 4MB, Format JPG, PNG, PDF
                    </p>
                  </div>
                </div>
                        </div>
                      </div>
                      <div className="row row-cols-xxl-5 row-cols-md-6">
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">First Name <span className="text-danger ms-1">*</span></label>
                    <input
                      type="text"
                      className={`form-control${fieldErrors.includes("first_name") ? " is-invalid" : ""}`}
                      name="first_name"
                      value={formData.first_name || ""}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Last Name <span className="text-danger ms-1">*</span></label>
                    <input
                      type="text"
                      className={`form-control${fieldErrors.includes("last_name") ? " is-invalid" : ""}`}
                      name="last_name"
                      value={formData.last_name || ""}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Assign Class(For Class Teacher) <span className="text-danger ms-1">*</span></label>
                    <CommonSelect
                      className={`select${fieldErrors.includes("class") ? " is-invalid" : ""}`}
                      options={classOptions}
                      value={formData.class || ""}
                      onChange={(value: any) => handleSelectChange("class", value)}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Subject <span className="text-danger ms-1">*</span></label>
                    <CommonSelectMulti
                      className={`select${fieldErrors.includes("subject") ? " is-invalid" : ""}`}
                      options={subjectOptions}
                      value={formData.subject || []}
                      onChange={(value: any) => handleSelectChange("subject", value)}
                      placeholder="Select subjects"
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Gender <span className="text-danger ms-1">*</span></label>
                    <CommonSelect
                      className={`select${fieldErrors.includes("gender") ? " is-invalid" : ""}`}
                      options={gender}
                      value={formData.gender || ""}
                      onChange={(value: any) => handleSelectChange("gender", value)}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Primary Contact Number <span className="text-danger ms-1">*</span></label>
                    <input
                      type="text"
                      className={`form-control${fieldErrors.includes("phone") ? " is-invalid" : ""}`}
                      name="phone"
                      value={formData.phone || ""}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Email Address <span className="text-danger ms-1">*</span></label>
                    <input
                      type="email"
                      className={`form-control${fieldErrors.includes("email") ? " is-invalid" : ""}`}
                      name="email"
                      value={formData.email || ""}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Blood Group <span className="text-danger ms-1">*</span></label>
                    <CommonSelect
                      className="select"
                      options={bloodGroup}
                      value={formData.blood_group || ""}
                      onChange={(value: any) => handleSelectChange("blood_group", value)}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Date of Joining <span className="text-danger ms-1">*</span></label>
                    <div className="input-icon position-relative">
                      <DatePicker
                        className={`form-control datetimepicker${fieldErrors.includes("date_of_joining") ? " is-invalid" : ""}`}
                        format={{ format: "DD-MM-YYYY", type: "mask" }}
                        value={formData.date_of_joining ? dayjs(formData.date_of_joining) : null}
                        onChange={date => handleDateChange("date_of_joining", date ? date.format("YYYY-MM-DD") : "")}
                        placeholder="Select Date"
                      />
                      <span className="input-icon-addon">
                        <i className="ti ti-calendar" />
                      </span>
                    </div>
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Father’s Name</label>
                    <input
                      type="text"
                      className="form-control"
                      name="father_name"
                      value={formData.father_name || ""}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Mother’s Name</label>
                    <input
                      type="text"
                      className="form-control"
                      name="mother_name"
                      value={formData.mother_name || ""}
                      onChange={handleInputChange}
                    />
                  </div>
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Date of Birth <span className="text-danger ms-1">*</span></label>
                    <div className="input-icon position-relative">
                      <DatePicker
                        className={`form-control datetimepicker${fieldErrors.includes("dob") ? " is-invalid" : ""}`}
                        format={{ format: "DD-MM-YYYY", type: "mask" }}
                        value={formData.dob ? dayjs(formData.dob) : null}
                        onChange={date => handleDateChange("dob", date ? date.format("YYYY-MM-DD") : "")}
                        placeholder="Select Date"
                      />
                      <span className="input-icon-addon">
                        <i className="ti ti-calendar" />
                      </span>
                    </div>
                  </div>
                </div>
                        <div className="col-xxl col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Marital Status</label>
                            <CommonSelect
                              className="select"
                              options={Marital}
                              value={formData.marital_status || ""}
                              onChange={value => handleSelectChange("marital_status", value)}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Language Known</label>
                            <TagsInput
                              value={formData.language_known || []}
                              onChange={value => setFormData((prev: any) => ({ ...prev, language_known: value }))}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Qualification</label>
                            <input
                              type="text"
                              className="form-control"
                              name="qualification"
                              value={formData.qualification || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Work Experience
                            </label>
                            <input
                              type="text"
                              className="form-control"
                              name="work_experience"
                              value={formData.work_experience || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Previous School if Any
                            </label>
                            <input
                              type="text"
                              className="form-control"
                              name="previous_school"
                              value={formData.previous_school || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Previous School Address
                            </label>
                            <input
                              type="text"
                              className="form-control"
                              name="previous_school_address"
                              value={formData.previous_school_address || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Previous School Phone No
                            </label>
                            <input
                              type="text"
                              className="form-control"
                              name="previous_school_phone"
                              value={formData.previous_school_phone || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl-3 col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Address</label>
                            <input
                              type="text"
                              className="form-control"
                              name="address"
                              value={formData.address || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl-3 col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Permanent Address
                            </label>
                            <input
                              type="text"
                              className="form-control"
                              name="permanent_address"
                              value={formData.permanent_address || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl-3 col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              PAN Number / ID Number
                            </label>
                            <input
                              type="text"
                              className="form-control"
                              name="pan_number"
                              value={formData.pan_number || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl-3 col-xl-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Status</label>
                            <CommonSelect
                              className="select"
                              options={status}
                              value={formData.status || ""}
                              onChange={value => handleSelectChange("status", value)}
                            />
                          </div>
                        </div>
                        <div className="col-xxl-12 col-xl-12">
                          <div className="mb-3">
                            <label className="form-label">Notes</label>
                            <textarea
                              className="form-control"
                              name="notes"
                              placeholder="Other Information"
                              rows={4}
                              value={formData.notes || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {/* /Personal Information */}
                </>

                <>
                  {/* Payroll */}
                  <div className="card">
                    <div className="card-header bg-light">
                      <div className="d-flex align-items-center">
                        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                          <i className="ti ti-user-shield fs-16" />
                        </span>
                        <h4 className="text-dark">Payroll</h4>
                      </div>
                    </div>
                    <div className="card-body pb-1">
                      <div className="row">
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">EPF No</label>
                            <input
                              type="text"
                              className="form-control"
                              name="epf_no"
                              value={formData.epf_no || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Basic Salary</label>
                            <input
                              type="text"
                              className="form-control"
                              name="basic_salary"
                              value={formData.basic_salary || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Contract Type</label>
                            <CommonSelect
                              className="select"
                              options={Contract}
                              value={formData.contract_type || ""}
                              onChange={value => handleSelectChange("contract_type", value)}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Work Shift</label>
                            <CommonSelect
                              className="select"
                              options={Shift}
                              value={formData.work_shift || ""}
                              onChange={value => handleSelectChange("work_shift", value)}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Work Location</label>
                            <input
                              type="text"
                              className="form-control"
                              name="work_location"
                              value={formData.work_location || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Date of Leaving
                            </label>
                            <div className="input-icon position-relative">
                              <DatePicker
                                className="form-control datetimepicker"
                                format={{ format: "DD-MM-YYYY", type: "mask" }}
                                value={formData.date_of_leaving ? dayjs(formData.date_of_leaving) : null}
                                onChange={date => handleDateChange("date_of_leaving", date ? date.format("YYYY-MM-DD") : "")}
                                placeholder="Select Date"
                              />
                              <span className="input-icon-addon">
                                <i className="ti ti-calendar" />
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {/* /Payroll */}
                  {/* Leaves */}
                  <div className="card">
                    <div className="card-header bg-light">
                      <div className="d-flex align-items-center">
                        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                          <i className="ti ti-users fs-16" />
                        </span>
                        <h4 className="text-dark">Leaves</h4>
                      </div>
                    </div>
                    <div className="card-body pb-1">
                      <div className="row">
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Medical Leaves</label>
                            <input
                              type="text"
                              className="form-control"
                              name="medical_leaves"
                              value={formData.medical_leaves || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Casual Leaves</label>
                            <input
                              type="text"
                              className="form-control"
                              name="casual_leaves"
                              value={formData.casual_leaves || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Maternity Leaves
                            </label>
                            <input
                              type="text"
                              className="form-control"
                              name="maternity_leaves"
                              value={formData.maternity_leaves || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Sick Leaves</label>
                            <input
                              type="text"
                              className="form-control"
                              name="sick_leaves"
                              value={formData.sick_leaves || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {/* /Leaves */}
                  {/* Bank Details */}
                  <div className="card">
                    <div className="card-header bg-light">
                      <div className="d-flex align-items-center">
                        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                          <i className="ti ti-map fs-16" />
                        </span>
                        <h4 className="text-dark">Bank Account Detail</h4>
                      </div>
                    </div>
                    <div className="card-body pb-1">
                      <div className="row">
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Account Name</label>
                            <input
                              type="text"
                              className="form-control"
                              name="account_name"
                              value={formData.account_name || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Account Number</label>
                            <input
                              type="text"
                              className="form-control"
                              name="account_number"
                              value={formData.account_number || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Bank Name</label>
                            <input
                              type="text"
                              className="form-control"
                              name="bank_name"
                              value={formData.bank_name || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">IFSC Code</label>
                            <input
                              type="text"
                              className="form-control"
                              name="ifsc_code"
                              value={formData.ifsc_code || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Branch Name</label>
                            <input
                              type="text"
                              className="form-control"
                              name="branch_name"
                              value={formData.branch_name || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {/* /Bank Details */}
                </>

                {/* Transport Information */}
                <div className="card">
                  <div className="card-header bg-light d-flex align-items-center justify-content-between">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-bus-stop fs-16" />
                      </span>
                      <h4 className="text-dark">Transport Information</h4>
                    </div>
                    <div className="form-check form-switch">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                      />
                    </div>
                  </div>
                  <div className="card-body pb-1">
                    <div className="row">
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Route</label>
                            <CommonSelect
                              className="select"
                              options={route}
                              value={formData.route || ""}
                              onChange={value => handleSelectChange("route", value)}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Vehicle Number</label>
                            <CommonSelect
                              className="select"
                              options={VehicleNumber}
                              value={formData.vehicle_number || ""}
                              onChange={value => handleSelectChange("vehicle_number", value)}
                            />
                          </div>
                        </div>
                        <div className="col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Pickup Point</label>
                            <CommonSelect
                              className="select"
                              options={PickupPoint}
                              value={formData.pickup_point || ""}
                              onChange={value => handleSelectChange("pickup_point", value)}
                            />
                          </div>
                        </div>
                    </div>
                  </div>
                </div>
                {/* /Transport Information */}
                {/* Hostel Information */}
                <div className="card">
                  <div className="card-header bg-light d-flex align-items-center justify-content-between">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-building-fortress fs-16" />
                      </span>
                      <h4 className="text-dark">Hostel Information</h4>
                    </div>
                    <div className="form-check form-switch">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                      />
                    </div>
                  </div>
                  <div className="card-body pb-1">
                    <div className="row">
                        <div className="col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Hostel</label>
                            <CommonSelect
                              className="select"
                              options={Hostel}
                              value={formData.hostel || ""}
                              onChange={value => handleSelectChange("hostel", value)}
                            />
                          </div>
                        </div>
                        <div className="col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Room No</label>
                            <CommonSelect
                              className="select"
                              options={roomNO}
                              value={formData.room_no || ""}
                              onChange={value => handleSelectChange("room_no", value)}
                            />
                          </div>
                        </div>
                    </div>
                  </div>
                </div>
                {/* /Hostel Information */}
                <>
                  {/* Social Media Links */}
                  <div className="card">
                    <div className="card-header bg-light">
                      <div className="d-flex align-items-center">
                        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                          <i className="ti ti-building fs-16" />
                        </span>
                        <h4 className="text-dark">Social Media Links</h4>
                      </div>
                    </div>
                    <div className="card-body pb-1">
                      <div className="row rows-cols-xxl-5">
                        <div className="col-xxl col-xl-3 col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Facebook</label>
                            <input
                              type="text"
                              className="form-control"
                              name="facebook"
                              value={formData.facebook || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Instagram</label>
                            <input
                              type="text"
                              className="form-control"
                              name="instagram"
                              value={formData.instagram || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Linked In</label>
                            <input
                              type="text"
                              className="form-control"
                              name="linkedin"
                              value={formData.linkedin || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Youtube</label>
                            <input
                              type="text"
                              className="form-control"
                              name="youtube"
                              value={formData.youtube || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                        <div className="col-xxl col-xl-3 col-lg-4 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Twitter URL</label>
                            <input
                              type="text"
                              className="form-control"
                              name="twitter"
                              value={formData.twitter || ""}
                              onChange={handleInputChange}
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {/* /Social Media Links */}
                  {/* Documents */}
                  <div className="card">
                    <div className="card-header bg-light">
                      <div className="d-flex align-items-center">
                        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                          <i className="ti ti-file fs-16" />
                        </span>
                        <h4 className="text-dark">Documents</h4>
                      </div>
                    </div>
                    <div className="card-body pb-1">
                      <div className="row">
                        <div className="col-lg-6">
                          <div className="mb-2">
                            <div className="mb-3">
                              <label className="form-label">
                                Upload Resume
                              </label>
                              <p>
                                Upload image size of 4MB, Accepted Format PDF
                              </p>
                            </div>
                            <div className="d-flex align-items-center flex-wrap">
                              <div className="btn btn-primary drag-upload-btn mb-2 me-2">
                                <i className="ti ti-file-upload me-1" />
                                Change
                                <input
                                  type="file"
                                  className="form-control image_sign"
                                  multiple
                                />
                              </div>
                              <p className="mb-2">Resume.pdf</p>
                            </div>
                          </div>
                        </div>
                        <div className="col-lg-6">
                          <div className="mb-2">
                            <div className="mb-3">
                              <label className="form-label">
                                Upload Joining Letter
                              </label>
                              <p>
                                Upload image size of 4MB, Accepted Format PDF
                              </p>
                            </div>
                            <div className="d-flex align-items-center flex-wrap">
                              <div className="btn btn-primary drag-upload-btn mb-2 me-2">
                                <i className="ti ti-file-upload me-1" />
                                Upload Document
                                <input
                                  type="file"
                                  className="form-control image_sign"
                                  multiple
                                />
                              </div>
                              <p className="mb-2">Resume.pdf</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {/* /Documents */}
                  {/* Password */}
                  <div className="card">
                    <div className="card-header bg-light">
                      <div className="d-flex align-items-center">
                        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                          <i className="ti ti-file fs-16" />
                        </span>
                        <h4 className="text-dark">Password</h4>
                      </div>
                    </div>
                    <div className="card-body pb-1">
                      <div className="row">
                        <div className="col-md-6">
                          <div className="mb-3">
                            <label className="form-label">New Password</label>
                            <input type="password" className="form-control" />
                          </div>
                        </div>
                        <div className="col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Confirm Password
                            </label>
                            <input type="password" className="form-control" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {/* /Password */}
                </>

                <div className="text-end">
                  <button type="button" className="btn btn-light me-3" disabled={loading}>
                    Cancel
                  </button>
                  <button type="submit" className="btn btn-primary" disabled={loading}>
                    {isEdit ? "Update Teacher" : "Add Teacher"}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      {/* /Page Wrapper */}
    </>
  );
};

export default TeacherForm;
