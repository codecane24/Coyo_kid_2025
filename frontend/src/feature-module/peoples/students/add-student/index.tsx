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
import SchoolTransportMedicalForm, { TransportMedicalFormData } from "./SchoolTransportMedicalForm";
import DocumentsForm from "./DocumentsForm";
import { FinancialInfoType } from "./FinancialDetailsForm";
import { createStudent, updateStudent } from "../../../../services/StudentData";
import { ParentInfo } from "./ParentsGuardianForm";
import qs from "qs"; 
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
  profileImage?: File | null;
};




const AddStudent = () => {
  const routes = all_routes;
const [files, setFiles] = useState<File[]>([]);


    const { formData, setFormData } = useAdmissionForm();
    const personalInfoRef = useRef<any>(null);
const [studentId, setStudentId] = useState<string>(""); // instead of string | null
function camelToSnake(str: string) {
  return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
}

const method = studentId ? 'put' : 'post';
const url = studentId ? `/student/${studentId}` : `/student`;
const updateStudentId = (id: string) => {
  setStudentId(id);
};
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
function toSnakeCaseTwo(obj: any): any {
  if (Array.isArray(obj)) {
    return obj
      .map(toSnakeCase)
      .filter((item) => item !== undefined && item !== "");
  }

  if (obj !== null && typeof obj === "object") {
    const newObj: Record<string, any> = {};

    for (const [key, value] of Object.entries(obj)) {
      const snakeKey = key.replace(/([A-Z])/g, "_$1").toLowerCase();
      const convertedValue = toSnakeCase(value);

      const isEmptyObject =
        convertedValue &&
        typeof convertedValue === "object" &&
        !Array.isArray(convertedValue) &&
        Object.keys(convertedValue).length === 0;

      const shouldInclude = convertedValue !== "" && !isEmptyObject;

      if (shouldInclude || convertedValue === null) {
        newObj[snakeKey] = convertedValue;
      }
    }

    return newObj;
  }

  return obj;
}




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
   languages: [] as string[], // ✅ force type
  profileImage: null as File | null, // ✅ new field for image
});
const [parentInfo, setParentInfo] = useState<ParentInfo>({
  fatherName: "",
  fatherPhone: "",
  fatherAadhar: "",
  fatherOccupation: "",
  fatherEmail: "", // 👈 add this
  fatherProfileImage: null,
  fatherAadharImage: null,
  motherName: "",
  motherPhone: "",
  motherAadhar: "",
  motherOccupation: "",
  motherEmail: "", // 👈 add this
  motherProfileImage: null,
  motherAadharImage: null,
  siblingSameSchool: "",
  siblingStudentIds: [""],
  guardians: [
    {
      name: "",
      phone: "",
      aadhar: "",
      occupation: "",
      relation: "",
      profileImage: null,
      aadharImage: null,
    },
  ],
});

const [parentImages, setParentImages] = useState<{
  fatherProfile?: File;
  fatherAadhar?: File;
  motherProfile?: File;
  motherAadhar?: File;
  guardianProfiles: File[];
  guardianAadhars: File[];
}>({
  guardianProfiles: [],
  guardianAadhars: [],
});
const [transportMedical, setTransportMedical] = useState<TransportMedicalFormData>({
  transportService: "",
  seriousDisease: "",
  seriousInjuries: [],
  allergies: [],
  medications: [],
  previousSchoolName: "",
  previousSchoolAddress: "",
});






const [addressInfo, setAddressInfo] = useState({
  permanent: {
    address: "",
    area: "",
    landmark: "",
    city: "",
    state: "",
    pincode: "",
  },
  current: {
    address: "",
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
const buildFormDataFromPayload = (payload: typeof personalInfo): FormData => {
  const formData = new FormData();

  for (const [key, value] of Object.entries(payload)) {
    const snakeKey = camelToSnake(key);

    if (key === "profileImage" && value instanceof File) {
      formData.append("profile_image", value); // ✅ Manual override
    } else if (Array.isArray(value)) {
      formData.append(snakeKey, JSON.stringify(value)); // ✅ serialize arrays
    } else if (value !== null && value !== undefined) {
      formData.append(snakeKey, value);
    }
  }

  formData.append("step", "step_1"); // ✅ optional
  return formData;
};
const buildFormDataFromPayload2 = (payload: any): FormData => {
  const formData = new FormData();

  const appendFormData = (data: any, parentKey = "") => {
    if (Array.isArray(data)) {
      data.forEach((item, index) => {
        appendFormData(item, `${parentKey}[${index}]`);
      });
    } else if (data instanceof File) {
      formData.append(parentKey, data);
    } else if (data !== null && typeof data === "object") {
      Object.entries(data).forEach(([key, value]) => {
        const snakeKey = camelToSnake(key);
        appendFormData(value, parentKey ? `${parentKey}[${snakeKey}]` : snakeKey);
      });
    } else {
      formData.append(parentKey, data ?? "");
    }
  };

  appendFormData(payload);
  formData.append("step", "step_2"); // If needed
  return formData;
};

const handleStep1Submit = async (
  finalPayload: FormData,
  studentId: string | null,
  setStudentId: (id: string) => void,
  setFormData: (fn: (prev: any) => any) => void,
  setCurrentStep: (step: number) => void
) => {
  try {
    let res;

    if (studentId) {
      res = await updateStudent(studentId, finalPayload); // PUT
    } else {
      console.log("=====> Test payload:", finalPayload.get("profile_image"));
      res = await createStudent(finalPayload); // POST
    }

    if (res?.data?.status === "false") {
      const validationErrors = res?.data?.errors;
      const errorMessage = res?.data?.message || "Something went wrong";

      if (validationErrors) {
        const allErrors = Object.entries(validationErrors)
          .map(([field, messages]) => `${field}: ${(messages as string[]).join(", ")}`)
          .join("\n");
        alert(`❌ Validation Failed:\n${allErrors}`);
      } else {
        alert(`❌ Error: ${errorMessage}`);
      }

      return;
    }

    const newStudentId = res?.student_id;
    if (!studentId && newStudentId) {
      setStudentId(newStudentId);
    }

    setFormData((prev) => ({
      ...prev,
      personalInfo: finalPayload, // This could be adjusted if needed
    }));

    setCurrentStep(2);
  } catch (error: any) {
    const response = error?.response;

    if (response?.status === 422 && response?.data?.errors) {
      const validationErrors = response.data.errors;

      const allErrors = Object.entries(validationErrors)
        .map(([field, messages]) =>
          Array.isArray(messages) ? `${field}: ${messages.join(", ")}` : `${field}: ${messages}`
        )
        .join("\n");

      alert(`❌ Validation Failed:\n${allErrors}`);
    } else {
      alert("❌ Server Error. Please try again later.");
    }
  }
};



const handleNextStep = async () => {
if (currentStep === 1) {
  const formData = buildFormDataFromPayload(personalInfo);

  console.log("✅ FormData to be sent:");
//  formData.forEach((value, key) => {
//   console.log(`${key}:`, value);
// });


  try {
    await handleStep1Submit(
      formData,
      studentId,
      setStudentId,
      setFormData,
      setCurrentStep
    );
  } catch (error) {
    console.error("❌ Step 1 Submit Error:", error);
  }
}

else if (currentStep === 2) {
  const payload = {
    ...parentInfo,
  };

  const formData = new FormData();

  formData.append("student_id", String(studentId ?? ""));
  formData.append("step", "step_2");
  formData.append("_method", "PUT");

  const toSnake = (str: string) =>
    str.replace(/([A-Z])/g, "_$1").toLowerCase();

  for (const [key, value] of Object.entries(parentInfo)) {
    const snakeKey = toSnake(key);

    if (value instanceof File) {
      formData.append(snakeKey, value);
    } else if (Array.isArray(value)) {
      if (key === "guardians") {
        value.forEach((guardian, index) => {
          Object.entries(guardian).forEach(([gKey, gValue]) => {
            const fullKey = `guardians[${index}][${toSnake(gKey)}]`;

            if (gValue instanceof File) {
              formData.append(fullKey, gValue);
            } else {
              formData.append(fullKey, String(gValue ?? ""));
            }
          });
        });
        // ❌ DO NOT send this (Laravel doesn't need this)
        // formData.append("guardians", JSON.stringify(value));
      } else if (key === "siblingStudentIds") {
        value.forEach((id, index) => {
          formData.append(`sibling_student_ids[${index}]`, String(id));
        });
      } else {
        // Other arrays
        value.forEach((item, index) => {
          formData.append(`${snakeKey}[${index}]`, String(item));
        });
      }
    } else if (typeof value === "object" && value !== null) {
      formData.append(snakeKey, JSON.stringify(value));
    } else {
      formData.append(snakeKey, String(value ?? ""));
    }
  }

  try {
    await updateStudent(studentId, formData);
    console.log("✅ FormData sent:");
    formData.forEach((val, key) => console.log(`${key}:`, val));
  } catch (error) {
    console.error("❌ Submit Error:", error);
  }

  setFormData((prev) => ({ ...prev, parentGuardianInfo: payload }));
  setCurrentStep(3);
}


if (currentStep === 3) {


  const payload = {
    step: "step_3",
    studentId: studentId,
    permanentAddress: addressInfo.permanent,
    currentAddress: addressInfo.current,
  };

  const formData = new FormData();

  const toSnakeCase = (str: string) =>
    str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);

  const appendFormData = (data: any, parentKey = "") => {
    for (const [key, value] of Object.entries(data)) {
      const snakeKey = toSnakeCase(key);
      const formKey = parentKey ? `${parentKey}[${snakeKey}]` : snakeKey;

      if (typeof value === "object" && value !== null) {
        appendFormData(value, formKey); // 🔁 Recursively snake_case nested keys
      } else {
        formData.append(formKey, String(value ?? ""));
      }
    }
  };

  appendFormData(payload);

  // Add _method only if needed (Laravel-style override)
  formData.append("_method", "PUT");

  try {
    await updateStudent(studentId, formData); // ✅ Send as FormData
    console.log("✅ Step 3 Payload sent as snake_case FormData");
    setFormData((prev) => ({ ...prev, addressInfo: payload }));
    setCurrentStep(4);
  } catch (error) {
    console.error("❌ Step 3 Submit Error:", error);
  }
}




else if (currentStep === 4) {
  const payload = {
    ...transportMedical,
    step: "step_4",
    studentId,
  };

  const toSnakeCase = (str: string) =>
    str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);

  const formData = new FormData();

 for (const [key, value] of Object.entries(payload)) {
  const snakeKey = toSnakeCase(key);

  if (
    Array.isArray(value) &&
    ["seriousInjuries", "allergies", "medications"].includes(key)
  ) {
    value.forEach((v) => formData.append(`${snakeKey}[]`, v));
  } else {
    if (value instanceof Blob) {
      formData.append(snakeKey, value);
    } else if (typeof value === "object" && value !== null) {
      formData.append(snakeKey, JSON.stringify(value));
    } else {
      formData.append(snakeKey, value ?? "");
    }
  }
}


  formData.append("_method", "PUT");

  try {
    await updateStudent(studentId, formData);
    console.log("✅ Step 4 submitted");
    setCurrentStep(5);
  } catch (error) {
    console.error("❌ Error in Step 4 submission", error);
  }
}

else if (currentStep === 5) {
  const payload = {
    ...documents, // 👈 documents should come from state shared via props
    step: "step_5",
    studentId,
  };

  const toSnakeCase = (str: string) =>
    str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);

  const formData = new FormData();

  for (const [key, value] of Object.entries(payload)) {
    const snakeKey = toSnakeCase(key);

    if (value instanceof Blob) {
      // ✅ File (PDF) upload
      formData.append(snakeKey, value);
    } else if (typeof value === "object" && value !== null) {
      formData.append(snakeKey, JSON.stringify(value));
    } else {
      formData.append(snakeKey, value ?? "");
    }
  }

  formData.append("_method", "PUT");

  try {
    await updateStudent(studentId, formData);
    console.log("✅ Step 5 submitted");
    setCurrentStep(6);
  } catch (error) {
    console.error("❌ Error in Step 5 submission", error);
  }
}

}

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
  currentStep={currentStep}
  files={files}  
setFiles={(val) => setFiles(val ? Array.from(val) : [])} 
/>

)}

{currentStep === 2  && (
<ParentsGuardianForm
    currentStep={currentStep}
  parentInfo={parentInfo}
  setParentInfo={setParentInfo}
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
    setFormData={setFormData}
    transportMedical={transportMedical}
        setTransportMedical={setTransportMedical} // ✅ Add this line
  />
)}

         
        {currentStep === 5 && (
 <DocumentsForm
  currentStep={currentStep}
  setCurrentStep={setCurrentStep}
  setFormData={setFormData} // optional if unused
  documents={documents}      // ✅ state in parent
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
