import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
// import { feeGroup, feesTypes, paymentType } from '../../../core/common/selectoption/selectoption'
import { DatePicker } from "antd";
import dayjs from "dayjs";
import { all_routes } from "../../../router/all_routes";
import {
  AdmissionNo,
  Hostel,
  PickupPoint,
  VehicleNumber,
  academicYear,
  allClass,
  allSection,
  bloodGroup,
  cast,
  gender,
  house,
  mothertongue,
  names,
  religion,
  rollno,
  roomNO,
  route,
  status,
} from "../../../../core/common/selectoption/selectoption";
import { TagsInput } from "react-tag-input-component";
import CommonSelect from "../../../../core/common/commonSelect";
import { useLocation } from "react-router-dom";
import { getClassesList } from "../../../../services/ClassData";
import FinancialDetailsForm from "./FinancialDetailsForm";
import { useAdmissionForm } from "../../../../context/AdmissionFormContext";

type ClassItem = {
  id: string;
  name: string; 
   
};
// at the top or in a separate types file
export type PersonalInfoType = {
  academicYear: string;
  admissionNo: string;
  admissionDate: string;
  rollNo: string;
  status: string;
  firstName: string;
  middleName: string;
  lastName: string;
  class: string;
  section: string;
  gender: string;
  dob: string;
  bloodGroup: string;
  house: string;
  religion: string;
  category: string;
  primaryContact: string;
  email: string;
  caste: string;
  suitableBatch: string;
  languagesKnown: string[];
};

export type FinancialInfoType = {
  // You can leave it empty for now
};


const AddStudent = () => {
  const routes = all_routes;
    const { formData, setFormData } = useAdmissionForm();
      const [personalInfo, setPersonalInfo] = useState({
  academicYear: "",
  admissionNo: "",
  admissionDate: "",
  rollNo: "",
  status: "",
  firstName: "",
  middleName: "",
  lastName: "",
  class: "",
  section: "",
  gender: "",
  dob: "",
  bloodGroup: "",
  house: "",
  religion: "",
  category: "",
  primaryContact: "",
  email: "",
  caste: "",
  suitableBatch: "",
  languages: [], // for TagsInput
});
type AdmissionFormData = {
  personalInfo: PersonalInfoType;
  financialInfo?: FinancialInfoType;
  // ... any other steps
};
    const formatted = dayjs(personalInfo.admissionDate); // wrap before use
  const [classOptions, setClassOptions] = useState<{ label: string; value: string }[]>([]);
const [showFinancialForm, setShowFinancialForm] = useState(false);
const handleNextStep = () => setShowFinancialForm(true);

 const [allClass, setAllClass] = useState<{ label: string; value: string }[]>([]);
  const [loading, setLoading] = useState(false);
  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [owner, setOwner] = useState<string[]>(['English','Spanish']);
  const [owner1, setOwner1] = useState<string[]>([]);
  const [owner2, setOwner2] = useState<string[]>([]);
  const [defaultDate, setDefaultDate] = useState<dayjs.Dayjs | null>(null);
  const [newContents, setNewContents] = useState<number[]>([0]);
  const location = useLocation();
  const addNewContent = () => {
    setNewContents([...newContents, newContents.length]);
  };
  const removeContent = (index:any) => {
    setNewContents(newContents.filter((_, i) => i !== index));
  };
  useEffect(() => {
  const fetchClasses = async () => {
    try {
      const data: ClassItem[] = await getClassesList();
      const formatted = data.map((cls) => ({
        label: cls.name,
        value: cls.id,
         
      }));
      setClassOptions(formatted);
    } catch (err) {
      console.error("Failed to fetch class list:", err);
    }
  };

  fetchClasses();

}, []);

  useEffect(() => {
    if (location.pathname === routes.editStudent ) {
      const today = new Date();
      const year = today.getFullYear();
      const month = String(today.getMonth() + 1).padStart(2, "0"); // Month is zero-based, so we add 1
      const day = String(today.getDate()).padStart(2, "0");
      const formattedDate = `${month}-${day}-${year}`;
      const defaultValue = dayjs(formattedDate);
      setIsEdit(true)
      setOwner(["English"])
      setOwner1(["Medecine Name"])
      setOwner2(["Allergy","Skin Allergy"])
      setDefaultDate(defaultValue)
      console.log(formattedDate,11);
      
    }else {
      setIsEdit(false)
      setDefaultDate(null)
    }
  }, [location.pathname])


const [files, setFiles] = useState<FileList | null>(null);
const formatDate = (dateStr: string | undefined) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return d.toISOString().split("T")[0]; // YYYY-MM-DD
};



const handleSubmitPersonalInfo = () => {
  if (!personalInfo.firstName || !personalInfo.admissionNo) {
    alert("First Name and Admission Number are required");
    return;
  }

  const payload = {
    ...personalInfo,
    languagesKnown: owner,
    admissionDate: formatDate(personalInfo.admissionDate),
    dob: formatDate(personalInfo.dob),
  };


  // ✅ If you're sending to backend
  const formData = new FormData();
  formData.append("data", JSON.stringify(payload));

  // ✅ Store in global form context
 setFormData({
  ...formData,
  personalInfo: payload,
});

  if (files) {
    Array.from(files).forEach((file: File) => {
      formData.append("images", file);
    });
  }

  // await axios.post("/api/admission", formData)

  // ✅ Move to next step if needed
  // goToNextStep(); // <- you control this
  console.log(payload)
};



  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">

        <div className="content content-two">
          {showFinancialForm ? (
  <FinancialDetailsForm/>
) : (
  <>
          {/* Page Header */}
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="mb-1">{isEdit?'Edit':'Add'} Student</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to={routes.studentList}>Students</Link>
                  </li>
                  <li className="breadcrumb-item active" aria-current="page">
                    {isEdit?'Edit':'Add'} Student
                  </li>
                </ol>
              </nav>
            </div>
          </div>
          {/* /Page Header */}
          <div className="row">
            <div className="col-md-12">
              <form>
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
                Upload
                <input
                  type="file"
                  className="form-control image-sign"
                  multiple
                  onChange={(e) => setFiles(e.target.files)}
                />
              </div>
              <Link to="#" className="btn btn-primary mb-3" onClick={() => setFiles(null)}>
                Remove
              </Link>
            </div>
            <p className="fs-12">
              Upload image size 4MB, Format JPG, PNG, SVG
            </p>
          </div>
        </div>
      </div>
    </div>

    <div className="row row-cols-xxl-5 row-cols-md-6">
      {/** Academic Year */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Academic Year</label>
          <CommonSelect
            className="select"
            options={academicYear}
            value={academicYear.find(option => option.value === personalInfo.academicYear)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, academicYear: option?.value || "" })}
          />
        </div>
      </div>

      {/** Admission Number */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Admission Number</label>
          <input
            type="text"
            className="form-control"
            value={personalInfo.admissionNo}
            onChange={(e) => setPersonalInfo({ ...personalInfo, admissionNo: e.target.value })}
          />
        </div>
      </div>

      {/** Admission Date */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Admission Date</label>
          <div className="input-icon position-relative">
     <DatePicker
  value={dayjs(personalInfo.admissionDate || new Date())}
  onChange={(date) => {
    setPersonalInfo((prev) => ({
      ...prev,
      admissionDate: dayjs(date).format("YYYY-MM-DD"),
    }));
  }}
/>

            <span className="input-icon-addon">
              <i className="ti ti-calendar" />
            </span>
          </div>
        </div>
      </div>

      {/** Roll Number */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Roll Number</label>
          <input
            type="text"
            className="form-control"
            value={personalInfo.rollNo}
            onChange={(e) => setPersonalInfo({ ...personalInfo, rollNo: e.target.value })}
          />
        </div>
      </div>

      {/** Status */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Status</label>
          <CommonSelect
            className="select"
            options={status}
            value={status.find(option => option.value === personalInfo.status)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, status: option?.value || "" })}
          />
        </div>
      </div>

      {/** First Name */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">First Name</label>
          <input
            type="text"
            className="form-control"
            value={personalInfo.firstName}
            onChange={(e) => setPersonalInfo({ ...personalInfo, firstName: e.target.value })}
          />
        </div>
      </div>

      {/** Middle Name */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Middle Name</label>
          <input
            type="text"
            className="form-control"
            value={personalInfo.middleName}
            onChange={(e) => setPersonalInfo({ ...personalInfo, middleName: e.target.value })}
          />
        </div>
      </div>

      {/** Last Name */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Last Name</label>
          <input
            type="text"
            className="form-control"
            value={personalInfo.lastName}
            onChange={(e) => setPersonalInfo({ ...personalInfo, lastName: e.target.value })}
          />
        </div>
      </div>

      {/** Class */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Class</label>
          <CommonSelect
            className="select"
            options={classOptions}
            value={classOptions.find(option => option.value === personalInfo.class)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, class: option?.value || "" })}
          />
        </div>
      </div>

      {/** Section */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Section</label>
          <CommonSelect
            className="select"
            options={allSection}
            value={allSection.find(option => option.value === personalInfo.section)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, section: option?.value || "" })}
          />
        </div>
      </div>

      {/** Gender */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Gender</label>
          <CommonSelect
            className="select"
            options={gender}
            value={gender.find(option => option.value === personalInfo.gender)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, gender: option?.value || "" })}
          />
        </div>
      </div>

      {/** Date of Birth */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Date of Birth</label>
          <div className="input-icon position-relative">
           
                <DatePicker
  value={dayjs(personalInfo.dob || new Date())}
  onChange={(date) => {
    setPersonalInfo((prev) => ({
      ...prev,
      dob: dayjs(date).format("YYYY-MM-DD"),
    }));
  }}
/>
            <span className="input-icon-addon">
              <i className="ti ti-calendar" />
            </span>
          </div>
        </div>
      </div>

      {/** Blood Group */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Blood Group</label>
          <CommonSelect
            className="select"
            options={bloodGroup}
            value={bloodGroup.find(option => option.value === personalInfo.bloodGroup)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, bloodGroup: option?.value || "" })}
          />
        </div>
      </div>

      {/** House */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">House</label>
          <CommonSelect
            className="select"
            options={house}
            value={house.find(option => option.value === personalInfo.house)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, house: option?.value || "" })}
          />
        </div>
      </div>

      {/** Religion */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Religion</label>
          <CommonSelect
            className="select"
            options={religion}
            value={religion.find(option => option.value === personalInfo.religion)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, religion: option?.value || "" })}
          />
        </div>
      </div>

      {/** Category */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Category</label>
          <CommonSelect
            className="select"
            options={cast}
            value={cast.find(option => option.value === personalInfo.category)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, category: option?.value || "" })}
          />
        </div>
      </div>

      {/** Contact Number */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Primary Contact Number</label>
          <input
            type="text"
            className="form-control"
            value={personalInfo.primaryContact}
            onChange={(e) => setPersonalInfo({ ...personalInfo, primaryContact: e.target.value })}
          />
        </div>
      </div>

      {/** Email */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Email Address</label>
          <input
            type="email"
            className="form-control"
            value={personalInfo.email}
            onChange={(e) => setPersonalInfo({ ...personalInfo, email: e.target.value })}
          />
        </div>
      </div>

      {/** Caste */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Caste</label>
          <input
            type="text"
            className="form-control"
            value={personalInfo.caste}
            onChange={(e) => setPersonalInfo({ ...personalInfo, caste: e.target.value })}
          />
        </div>
      </div>

      {/** Suitable Batch */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Suitable Batch</label>
          <CommonSelect
            className="select"
            options={mothertongue}
            value={mothertongue.find(option => option.value === personalInfo.suitableBatch)}
            onChange={(option) => setPersonalInfo({ ...personalInfo, suitableBatch: option?.value || "" })}
          />
        </div>
      </div>

      {/** Languages Known */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Language Known</label>
          <TagsInput
            value={owner}
            onChange={(val) => setOwner(val)}
          />
        </div>
      </div>
    </div>
  </div>
</div>

                
                {/* Parents & Guardian Information */}
                <div className="card">
                  <div className="card-header bg-light">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-user-shield fs-16" />
                      </span>
                      <h4 className="text-dark">
                        Parents &amp; Guardian Information
                      </h4>
                    </div>
                  </div>
                  <div className="card-body pb-0">
                    <div className="border-bottom mb-3">
                      <h5 className="mb-3">Father’s Info</h5>
                      <div className="row">
                        <div className="col-md-12">
                          <div className="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                            <div className="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                              <i className="ti ti-photo-plus fs-16" />
                            </div>
                            <div className="profile-upload">
                              <div className="profile-uploader d-flex align-items-center">
                                <div className="drag-upload-btn mb-3">
                                  Upload
                                  <input
                                    type="file"
                                    className="form-control image-sign"
                                    multiple
                                  />
                                </div>
                                <Link to="#" className="btn btn-primary mb-3">
                                  Remove
                                </Link>
                              </div>
                              <p className="fs-12">
                                Upload image size 4MB, Format JPG, PNG, SVG
                              </p>
                            </div>
                          </div>
                        </div>
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Father Name</label>
                            <input type="text" className="form-control" defaultValue={isEdit? 'Jerald Vicinius': undefined}/>
                          </div>
                        </div>
                          <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Phone Number</label>
                            <input type="text" className="form-control" defaultValue={isEdit? '+1 45545 46464': undefined}/>
                          </div>
                        </div>
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Adhar Number</label>
                            <input type="text" className="form-control" defaultValue={isEdit? 'jera@example.com': undefined}/>
                          </div>
                        </div>
                      
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Father Occupation
                            </label>
                            <input type="text" className="form-control" defaultValue={isEdit? 'Mechanic': undefined}/>
                          </div>
                        </div>
                          <div className="profile-upload">
                              <div className="profile-uploader d-flex align-items-center">
                                    <label className="form-label">ITR Scan Copy</label>
                                <div className="drag-upload-btn mb-3">
                                  Upload
                                  <input
                                    type="file"
                                    className="form-control image-sign"
                                    multiple
                                  />
                                </div>
                                <Link to="#" className="btn btn-primary mb-3">
                                  Remove
                                </Link>
                              </div>
                              <p className="fs-12">
                               most Recent ITR Shuld Be Ulploaded
                              </p>
                            </div>
                      </div>
                    </div>
                    <div className="border-bottom mb-3">
                      <h5 className="mb-3">Mother’s Info</h5>
                      <div className="row">
                        <div className="col-md-12">
                          <div className="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                            <div className="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                              <i className="ti ti-photo-plus fs-16" />
                            </div>
                            <div className="profile-upload">
                              <div className="profile-uploader d-flex align-items-center">
                                <div className="drag-upload-btn mb-3">
                                  Upload
                                  <input
                                    type="file"
                                    className="form-control image-sign"
                                    multiple
                                  />
                                </div>
                                <Link to="#" className="btn btn-primary mb-3">
                                  Remove
                                </Link>
                              </div>
                              <p className="fs-12">
                                Upload image size 4MB, Format JPG, PNG, SVG
                              </p>
                            </div>
                          </div>
                        </div>
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Mother Name</label>
                            <input type="text" className="form-control" defaultValue={isEdit? 'Roberta Webber': undefined}/>
                          </div>
                        </div>
                           <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Phone Number</label>
                            <input type="text" className="form-control" defaultValue={isEdit? '+1 46499 24357': undefined}/>
                          </div>
                        </div>
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">Adhar Number</label>
                            <input type="text" className="form-control" defaultValue={isEdit? 'robe@example.com': undefined}/>
                          </div>
                        </div>
                     
                        <div className="col-lg-3 col-md-6">
                          <div className="mb-3">
                            <label className="form-label">
                              Mother Occupation
                            </label>
                            <input type="text" className="form-control" defaultValue={isEdit? 'Homemaker': undefined}/>
                          </div>
                        </div>
                          <div className="profile-upload">
                              <div className="profile-uploader d-flex align-items-center">
                                    <label className="form-label">ITR Scan Copy</label>
                                <div className="drag-upload-btn mb-3">
                                  Upload
                                  <input
                                    type="file"
                                    className="form-control image-sign"
                                    multiple
                                  />
                                </div>
                                <Link to="#" className="btn btn-primary mb-3">
                                  Remove
                                </Link>
                              </div>
                              <p className="fs-12">
                               most Recent ITR Shuld Be Ulploaded
                              </p>
                            </div><br></br>
                      </div>
                    </div>
                    <div>
   
                     
                    </div>
                  </div>
                </div>
                
                {/* Sibilings */}
                <div className="card">
                  <div className="card-header bg-light">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-users fs-16" />
                      </span>
                      <h4 className="text-dark">Sibilings</h4>
                    </div>
                  </div>
                  <div className="card-body">
      <div className="addsibling-info">
        <div className="row">
          <div className="col-md-12">
            <div className="mb-2">
              <label className="form-label">Sibling Info</label>
              <div className="d-flex align-items-center flex-wrap">
                <label className="form-label text-dark fw-normal me-2">
                  Is Sibling studying in the same school
                </label>
                <div className="form-check me-3 mb-2">
                  <input
                    className="form-check-input"
                    type="radio"
                    name="sibling"
                    id="yes"
                    defaultChecked
                  />
                  <label className="form-check-label" htmlFor="yes">
                    Yes
                  </label>
                </div>
                <div className="form-check mb-2">
                  <input
                    className="form-check-input"
                    type="radio"
                    name="sibling"
                    id="no"
                  />
                  <label className="form-check-label" htmlFor="no">
                    No
                  </label>
                </div>
              </div>
            </div>
          </div>
          {newContents.map((_, index) => (
            <div key={index} className="col-lg-12">
              <div className="row">
                <div className="col-lg-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Name</label>
                    <CommonSelect
                      className="select"
                      options={names}
                      defaultValue={isEdit?names[0]:undefined}
                    />
                  </div>
                </div>
                  <div className="col-lg-3 col-md-6">
                  <div className="mb-3">
                    <div className="d-flex align-items-center">
                      <div className="w-100">
                        <label className="form-label">Class</label>
                        <CommonSelect
                          className="select"
                          options={allClass}
                          defaultValue={isEdit?allClass[0]:undefined}
                        />
                      </div>
                      {newContents.length > 1 && (
                        <div>
                          <label className="form-label">&nbsp;</label>
                          <Link
                            to="#"
                            className="trash-icon ms-3"
                            onClick={() => removeContent(index)}
                          >
                            <i className="ti ti-trash-x" />
                          </Link>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
                  <div className="col-lg-3 col-md-6">
                  <div className="mb-3">
                    <div className="d-flex align-items-center">
                      <div className="w-100">
                        <label className="form-label">Section</label>
                        <CommonSelect
                          className="select"
                          options={allClass}
                          defaultValue={isEdit?allClass[0]:undefined}
                        />
                      </div>
                      {newContents.length > 1 && (
                        <div>
                          <label className="form-label">&nbsp;</label>
                          <Link
                            to="#"
                            className="trash-icon ms-3"
                            onClick={() => removeContent(index)}
                          >
                            <i className="ti ti-trash-x" />
                          </Link>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
                <div className="col-lg-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Roll No</label>
                    <CommonSelect
                      className="select"
                      options={rollno}
                      defaultValue={isEdit?rollno[0]:undefined}
                    />
                  </div>
                </div>
               
                <div className="col-lg-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Admission No</label>
                    <CommonSelect
                      className="select"
                      options={AdmissionNo}
                      defaultValue={isEdit?AdmissionNo[0]:undefined}
                    />
                  </div>
                </div>
               
              </div>
            </div>
          ))}
        </div>
      </div>
      <div className="border-top pt-3">
        <Link
          to="#"
          onClick={addNewContent}
          className="add-sibling btn btn-primary d-inline-flex align-items-center"
        >
          <i className="ti ti-circle-plus me-2" />
          Add New
        </Link>
      </div>
    </div>
                </div>
             
                {/* Address */}
                  <div className="card">
                  <div className="card-header bg-light">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-map fs-16" />
                      </span>
                      <h4 className="text-dark">Permanent Address</h4>
                    </div>
                  </div>
                  <div className="card-body pb-1">
                    <div className="row">
                        <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                           House No. & Colony Name
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>  
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                      Area
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                         <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                          Landmark
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                           <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                         City
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                           <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                      State
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                         <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                          Pincode
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                   <div className="card">
                  <div className="card-header bg-light">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-map fs-16" />
                      </span>
                      <h4 className="text-dark">Current Address</h4>
                    </div>
                  </div>
                  <div className="card-body pb-1">
                    <div className="row">
                        <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                            No-Colony
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>  
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                      Area
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                         <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                          Landmark
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                           <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                         City
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                           <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                      State
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                         <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                          Pincode
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '3495 Red Hawk Road, Buffalo Lake, MN 55314': undefined}/>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
           
                {/* Transport Information */}
                <div className="card">
                  <div className="card-header bg-light d-flex align-items-center justify-content-between">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-bus-stop fs-16" />
                      </span>
                      <h4 className="text-dark">Transport Information</h4>
                    </div>
                
                  </div>
              
                  <div className="mb-3 pl-3">
                              <label className="form-label">Avail Transport Service</label>
   
  <div>
    <div className="form-check form-check-inline">
      <input
        className="form-check-input"
        type="radio"
        name="ownHouse"
        id="ownHouseYes"
        value="yes"
      />
      <label className="form-check-label" htmlFor="ownHouseYes">
        Yes
      </label>
    </div>
    <div className="form-check form-check-inline">
      <input
        className="form-check-input"
        type="radio"
        name="ownHouse"
        id="ownHouseNo"
        value="no"
      />
      <label className="form-check-label" htmlFor="ownHouseNo">
        No
      </label>
    </div>
  </div>
</div>

                </div>
               
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
                            <label className="form-label mb-1">
                              Birth Certificate
                            </label>
                            <p>Upload image size of 4MB, Accepted Format PDF</p>
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
                            {isEdit? <p className="mb-2">BirthCertificate.pdf</p> : <></>}
                            
                          </div>
                        </div>
                      </div>
                        <div className="col-lg-6">
                        <div className="mb-2">
                          <div className="mb-3">
                            <label className="form-label mb-1">
                             Adhar Card
                            </label>
                            <p>Upload image size of 4MB, Accepted Format PDF</p>
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
                            {isEdit? <p className="mb-2">BirthCertificate.pdf</p> : <></>}
                            
                          </div>
                        </div>
                      </div>
                      <div className="col-lg-6">
                        <div className="mb-2">
                          <div className="mb-3">
                            <label className="form-label mb-1">
                              Upload Transfer Certificate
                            </label>
                            <p>Upload image size of 4MB, Accepted Format PDF</p>
                          </div>
                          <div className="d-flex align-items-center flex-wrap">
                            <div className="btn btn-primary drag-upload-btn mb-2">
                              <i className="ti ti-file-upload me-1" />
                              Upload Document
                              <input
                                type="file"
                                className="form-control image_sign"
                                multiple
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            
                {/* Medical History */}
                <div className="card">
                  <div className="card-header bg-light">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-medical-cross fs-16" />
                      </span>
                      <h4 className="text-dark">Medical History</h4>
                    </div>
                  </div>
                  <div className="card-body pb-1">
                    <div className="row">
                      <div className="col-md-12">
                   
                         <div className="mb-2">
                     <label className="form-label">
                            Medical History
                          </label>
                          <div className="d-flex align-items-center flex-wrap">
                            <label className="form-label text-dark fw-normal me-2">
            Any serious disease in past?
                            </label>
                            <div className="form-check me-3 mb-2">
                              <input
                                className="form-check-input"
                                type="radio"
                                name="condition"
                                id="good"
                                defaultChecked
                              />
                              <label
                                className="form-check-label"
                                htmlFor="good"
                              >
                               Yes
                              </label>
                            </div>
                            <div className="form-check me-3 mb-2">
                              <input
                                className="form-check-input"
                                type="radio"
                                name="condition"
                                id="bad"
                              />
                              <label className="form-check-label" htmlFor="bad">
                            NO
                              </label>
                            </div>
                        
                          </div>
                     
                        </div>
                      </div>
                            <div className="mb-3">
                        <label className="form-label">Any Serious Injury In Past?</label>

                        
                        <TagsInput
                            // className="input-tags form-control"
                            value={owner2}
                            onChange={setOwner2}
                          />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Allergies</label>
                        
                        <TagsInput
                            // className="input-tags form-control"
                            value={owner2}
                            onChange={setOwner2}
                          />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Medications</label>
                        <TagsInput
                            // className="input-tags form-control"
                            value={owner1}
                            onChange={setOwner1}
                          />
                      </div>
                    </div>
                  </div>
                </div>
              
                {/* Previous School details */}
                <div className="card">
                  <div className="card-header bg-light">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
                        <i className="ti ti-building fs-16" />
                      </span>
                      <h4 className="text-dark">Previous School Details</h4>
                    </div>
                  </div>
                  <div className="card-body pb-1">
                    <div className="row">
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">School Name</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'Oxford Matriculation, USA': undefined}/>
                        </div>
                      </div>
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Address</label>
                          <input type="text" className="form-control" defaultValue={isEdit? '1852 Barnes Avenue, Cincinnati, OH 45202': undefined}/>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              
                {/* /Other Details */}
           <div className="form-check mt-3">
  <input
    className="form-check-input"
    type="checkbox"
    id="agreeTerms"
  />
  <label className="form-check-label" htmlFor="agreeTerms">
    <p style={{ color: "#333", marginBottom: 0 }}>
      We Agree With Terms And Conditions Of Organisation.
    </p>
  </label>
</div>
<div className="d-flex justify-content-end mt-4">
  <button
    className="btn btn-primary"
    style={{ backgroundColor: "#0d6efd", borderColor: "#0d6efd" }} // Light Bootstrap blue
   onClick={handleSubmitPersonalInfo}
  >
    <i className="bi bi-arrow-right-circle me-2" />
    Next Step
  </button>
</div>

              </form>
            </div>
          </div>
          </>
)}
        </div>

      </div>
     
    </>
  );
};

export default AddStudent;
