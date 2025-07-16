import React, { useEffect, useState, useRef } from "react";
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
import MultiStepProgressBar from "../../../../core/common/MultiStepProgressBar";
import PersonalInfoForm from "./PersonalInfoForm";
import ParentsGuardianForm from "./ParentsGuardianForm";


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
    const personalInfoRef = useRef<any>(null);

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
const [parentInfo, setParentInfo] = useState({
  fatherName: "",
  fatherPhone: "",
  fatherAdhar: "",
  fatherOccupation: "",
  motherName: "",
  motherPhone: "",
  motherAdhar: "",
  motherOccupation: "",
  siblingSameSchool: "", // "yes" or "no"
});

type AdmissionFormData = {
  personalInfo: PersonalInfoType;
  financialInfo?: FinancialInfoType;
  // ... any other steps
};
    const formatted = dayjs(personalInfo.admissionDate); // wrap before use
  const [classOptions, setClassOptions] = useState<{ label: string; value: string }[]>([]);
const [showFinancialForm, setShowFinancialForm] = useState(false);


 const [allClass, setAllClass] = useState<{ label: string; value: string }[]>([]);
  const [loading, setLoading] = useState(false);
  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [owner, setOwner] = useState<string[]>(['English','Spanish']);
  const [owner1, setOwner1] = useState<string[]>([]);
  const [owner2, setOwner2] = useState<string[]>([]);
  const [defaultDate, setDefaultDate] = useState<dayjs.Dayjs | null>(null);

  const location = useLocation();
const [newContents, setNewContents] = useState([{ name: "", class: "", section: "", rollNo: "", admissionNo: "" }]);

const totalSteps = 5;

  const addNewContent = () => {
  setNewContents([...newContents, { name: "", class: "", section: "", rollNo: "", admissionNo: "" }]);
};

const removeContent = (index: number) => {
  const updated = [...newContents];
  updated.splice(index, 1);
  setNewContents(updated);
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





const handleSubmitPersonalInfo = (e: React.FormEvent) => {
  e.preventDefault(); // 🔥 This prevents page refresh

  if (!personalInfo.firstName || !personalInfo.admissionNo) {
    alert("First Name and Admission Number are required");
    return;
  }

  const payload = {
    ...personalInfo,
    admissionDate: formatDate(personalInfo.admissionDate),
    dob: formatDate(personalInfo.dob),
    languages: owner,
  };

  setFormData((prev) => ({
    ...prev,
    personalInfo: payload,
  }));

  if (files?.length) {
    const formData = new FormData();
    formData.append("data", JSON.stringify(payload));
    Array.from(files).forEach((file) => {
      formData.append("images", file);
    });
    // Optional: await axios.post(...)
  }

  console.log("Payload:Add Student-PersionalInfo", payload);
  setShowFinancialForm(true);
};




const handleBack = () => {
  if (currentStep > 1) {
    setCurrentStep(currentStep - 1);
  }
};


const steps = [
  "Personal Information",
  "Parents & Guardian  Information",
  " Address",
  "Other Info",
  "Documents",
  "Financial Details"
];

const [currentStep, setCurrentStep] = useState(1); // use index (0-based)


const handleNextStep = () => {
  if (currentStep === 1) {
    const payload = {
      ...personalInfo,
      languages: owner,
    };

    console.log("✅ Step 1 Payload: Personal Info", payload);
    setFormData((prev) => ({ ...prev, personalInfo: payload }));
    setCurrentStep(2);
  }

  if (currentStep === 2) {
    const payload = {
      ...parentInfo,
      siblings: newContents,
    };

    console.log("✅ Step 2 Payload: Parent & Guardian Info", payload);
    setFormData((prev) => ({ ...prev, parentGuardianInfo: payload }));
    setCurrentStep(3);
  }
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
          <MultiStepProgressBar currentStep={currentStep} steps={steps} />
          {/* /Page Header */}
          <div className="row">
            <div className="col-md-12">
              <form onSubmit={(e) => e.preventDefault()}>
   
{currentStep === 1 && (
<PersonalInfoForm
  personalInfo={personalInfo}
  setPersonalInfo={setPersonalInfo}
  classOptions={classOptions}
  owner={owner}
  setOwner={setOwner}
  files={files}
  setFiles={setFiles}
/>

)}

{currentStep === 2 && (
  <ParentsGuardianForm
    currentStep={currentStep}
    setCurrentStep={setCurrentStep}
    setFormData={setFormData}
    parentInfo={parentInfo}
    setParentInfo={setParentInfo}
    isEdit={isEdit}
    newContents={newContents}
    addNewContent={addNewContent}
    removeContent={removeContent}
    allClass={allClass}
    names={names}
    rollno={rollno}
    AdmissionNo={AdmissionNo}
  />
)}




             
                {/* Address */}
                  {/* <div className="card">
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
            */}
                {/* Transport Information */}
                {/* <div className="card">
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

                </div> */}
               
                {/* Documents */}
                {/* <div className="card">
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
                </div> */}
            
                {/* Medical History */}
                {/* <div className="card">
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
                </div> */}
              
                {/* Previous School details */}
                {/* <div className="card">
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
                </div> */}
              
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
<div className="d-flex justify-content-between mt-4">
  {currentStep > 1 && (
    <button className="btn btn-secondary" onClick={handleBack}>
      <i className="bi bi-arrow-left-circle me-2" />
      Back
    </button>
  )}
  <button className="btn btn-primary" onClick={handleNextStep}>
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
