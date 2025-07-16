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
import AddressForm from "./AddressForm";
import SchoolTransportMedicalForm from "./SchoolTransportMedicalForm";
import DocumentsForm from "./DocumentsForm";
import { FinancialInfoType } from "./FinancialDetailsForm";
import { createStudent } from "../../../../services/StudentData";

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




const AddStudent = () => {
  const routes = all_routes;
    const { formData, setFormData } = useAdmissionForm();
    const personalInfoRef = useRef<any>(null);
const toSnakeCase = (obj: any): any => {
  if (Array.isArray(obj)) {
    return obj.map(toSnakeCase);
  } else if (obj !== null && typeof obj === "object") {
    return Object.keys(obj).reduce((acc: any, key: string) => {
      const snakeKey = key.replace(/([A-Z])/g, "_$1").toLowerCase();
      acc[snakeKey] = toSnakeCase(obj[key]);
      return acc;
    }, {});
  }
  return obj;
};

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
const [addressInfo, setAddressInfo] = useState({
  permanent: {
    houseNo: "",
    area: "",
    landmark: "",
    city: "",
    state: "",
    pincode: "",
  },
  current: {
    houseNo: "",
    area: "",
    landmark: "",
    city: "",
    state: "",
    pincode: "",
  },
});
const [documents, setDocuments] = useState<{
  birthCertificate: File | null;
  aadharCard: File | null;
  transferCertificate: File | null;
}>({
  birthCertificate: null,
  aadharCard: null,
  transferCertificate: null,
});
const [financialData, setFinancialData] = useState<FinancialInfoType>([]);




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
const [studentId, setStudentId] = useState("");

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
const handleNextStep = async () => {
  if (currentStep === 1) {
    const payload = {
      ...personalInfo,
      languages: owner,
    };

    const snakePayload = toSnakeCase(payload);

    const finalPayload = {
      step: "step_1",
      student_id: "", // empty in step 1
      ...snakePayload,
    };

    console.log("✅ Step 1 Payload (Snake Case):", finalPayload);

    try {
      const res = await createStudent(finalPayload);
      if (res?.data?.student_id) {
        setStudentId(res.data.student_id); // Save for next steps
      }
    } catch (error) {
      console.error("❌ Step 1 Submit Error:", error);
    }

    setFormData((prev) => ({ ...prev, personalInfo: snakePayload }));
    setCurrentStep(2);
  }

  else if (currentStep === 2) {
    const payload = {
      ...parentInfo,
      siblings: newContents,
    };

    const finalPayload = {
      step: "step_2",
      student_id: studentId,
      ...toSnakeCase(payload),
    };

    try {
      await createStudent(finalPayload);
    } catch (error) {
      console.error("❌ Step 2 Submit Error:", error);
    }

    console.log("✅ Step 2 Payload: Parent & Guardian Info", payload);
    setFormData((prev) => ({ ...prev, parentGuardianInfo: payload }));
    setCurrentStep(3);
  }

  else if (currentStep === 3) {
    const payload = {
      permanentAddress: addressInfo.permanent,
      currentAddress: addressInfo.current,
    };

    const finalPayload = {
      step: "step_3",
      student_id: studentId,
      ...toSnakeCase(payload),
    };

    try {
      await createStudent(finalPayload);
    } catch (error) {
      console.error("❌ Step 3 Submit Error:", error);
    }

    console.log("✅ Step 3 Payload: Address Info", payload);
    setFormData((prev) => ({ ...prev, addressInfo: payload }));
    setCurrentStep(4);
  }

  else if (currentStep === 4) {
    // If needed, add backend call here too.
    setCurrentStep(5);
  }

  else if (currentStep === 5) {
    const payload = { ...documents };

    const finalPayload = {
      step: "step_5",
      student_id: studentId,
      ...toSnakeCase(payload),
    };

    try {
      await createStudent(finalPayload);
    } catch (error) {
      console.error("❌ Step 5 Submit Error:", error);
    }

    console.log("✅ Step 5 Payload: Documents", payload);
    setFormData((prev) => ({ ...prev, documents: payload }));
    setCurrentStep(6);
  }

  else if (currentStep === 6) {
    const payload = financialData;

    const finalPayload = {
      step: "step_6",
      student_id: studentId,
      ...toSnakeCase({ financial_entries: payload }), // backend expects key wrapping
    };

    try {
      await createStudent(finalPayload);
    } catch (error) {
      console.error("❌ Step 6 Submit Error:", error);
    }

    console.log("✅ Step 6 Payload: Financial Info", payload);
    setFormData((prev) => ({
      ...prev,
      financialInfo: payload,
    }));
  }
};





  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">


        <div className="content content-two">

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




             
          {currentStep === 3 && (
  <AddressForm
    currentStep={currentStep}
    setCurrentStep={setCurrentStep}
    setFormData={setFormData}
    addressInfo={addressInfo}
    setAddressInfo={setAddressInfo}
    isEdit={isEdit}
  />
)}

           
      {currentStep === 4 && (
  <SchoolTransportMedicalForm
    currentStep={currentStep}
    setCurrentStep={setCurrentStep}
    setFormData={setFormData}
    isEdit={isEdit}
    formData={formData}
  />
)}
         
        {currentStep === 5 && (
  <DocumentsForm
    currentStep={currentStep}
    setCurrentStep={setCurrentStep}
    setFormData={setFormData}
    documents={documents}
    setDocuments={setDocuments}
    isEdit={isEdit}
  />
)}

{currentStep === 6 && (
<FinancialDetailsForm
  financialData={financialData}
  setFinancialData={setFinancialData}
  setFormData={setFormData}
  currentStep={currentStep}
  setCurrentStep={setCurrentStep}
  isEdit={isEdit}
/>

)}


          
              
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
<button className="btn btn-success" onClick={handleNextStep}>
  <i className="bi bi-check-circle me-2" />
  {currentStep === 6 ? "Submit" : "Next Step"}
</button>

</div>

              </form>
            </div>
          </div>
          </>
        </div>

      </div>
     
    </>
  );
};

export default AddStudent;
