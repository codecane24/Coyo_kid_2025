import React, { useEffect, useState, useRef } from "react";
import { Link,useParams  } from "react-router-dom";
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
import { createStudent, getStudentById, updateStudent } from "../../../../services/StudentData";
import { ParentInfo } from "./ParentsGuardianForm";
import qs from "qs"; 
import { toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { getAllId } from "../../../../services/GetAllId";
import { motion } from "framer-motion";
import { getInquiryById } from "../../../../services/AdmissionInquiry";
import FinancialSummary from "./FinancialSummary";
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
const { id } = useParams();
const isEditMode = Boolean(id);
const location = useLocation();
const inquiryId = location.state?.inquiryId || null;


  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [inquiryData, setInquiryData] = useState<any>(null);
const [inquiryConversionMode, setInquiryConversionMode] = useState<boolean>(false);


const params = useParams();
const { editstudentId } = useParams<{ editstudentId: string }>();


const [editStudentData, setEditStudentData] = useState<any>(null);


const [files, setFiles] = useState<File[]>([]);
    const { formData, setFormData } = useAdmissionForm();
    const personalInfoRef = useRef<any>(null);

const [studentId, setStudentId] = useState<string>(""); // instead of string | null

function camelToSnake(str: string) {
  return str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
}

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
  siblingSameSchool: "no", // default
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
  seriousDisease: [],
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

  const [owner, setOwner] = useState<string[]>(['English','Hindi',]);
  const [owner1, setOwner1] = useState<string[]>([]);
  const [owner2, setOwner2] = useState<string[]>([]);
  const [defaultDate, setDefaultDate] = useState<dayjs.Dayjs | null>(null);



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
console.log(inquiryConversionMode)
useEffect(() => {
  if (inquiryConversionMode) {
    setLoading(true);
    getInquiryById(inquiryId)
      .then((res) => {
        console.log("this is the inquiry conversion data",res.data)
     
      })
      .catch((err) => {
        console.error("Error fetching inquiry:", err);
      
      })
      .finally(() => setLoading(false));
  }
}, [inquiryConversionMode]);
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

/**
 * Step-1 submit handler — works for BOTH create and edit flows.
 *
 * @param finalPayload  the FormData you built
 * @param studentId     (state)   id returned after a brand-new create (null until you get it)
 * @param routeStudentId          id that comes from the URL when you’re editing
 * @param setStudentId   setter to store a freshly-created id
 * @param setFormData    lift state up to the wizard context
 * @param setCurrentStep move to the next step in the wizard
 */

const [studentData, setStudentData] = useState<any>(null);
const { id: routeStudentId } = useParams();


const isEditingMode = location.pathname.includes("/student/edit");

console.log("routeStudentId:", routeStudentId);
console.log("isEditingMode:", isEditingMode);

useEffect(() => {
  if (inquiryId) {
    setInquiryConversionMode(true);
    setLoading(true);
    getInquiryById(inquiryId)
      .then((res) => {
        console.log("this is the inquiry conversion data", res.data);
        setInquiryData(res.data);
      })
      .catch((err) => console.error("Error fetching inquiry:", err))
      .finally(() => setLoading(false));
  }
}, [inquiryId]);

useEffect(() => {
  if (isEditingMode && routeStudentId) {
    console.log("Editing student with ID:", routeStudentId);
    getStudentById(routeStudentId)
      .then((data) => {
        console.log("Student fetched:", data);
        setStudentData(data);
      })
      .catch((err) => console.error(err));
  }
}, [isEditingMode, routeStudentId]);
const StudentEditCode = studentData?.data?.step_1?.code || null;
useEffect(() => {
  if (routeStudentId) {
    setStudentId(routeStudentId);
  }
}, [routeStudentId]);



const handleStep1Submit = async (
  finalPayload: FormData,
  studentId: string | null,
  routeStudentId: string | undefined,
  setStudentId: React.Dispatch<React.SetStateAction<string>>,
  setFormData: React.Dispatch<React.SetStateAction<any>>,
  setCurrentStep: React.Dispatch<React.SetStateAction<number>>
) => {
  try {
    const existingId = routeStudentId || studentId;
    let res;

    if (existingId) {
      res = await updateStudent(existingId, finalPayload);
    } else {
      res = await createStudent(finalPayload);
    }

    if (res?.data?.status === "false") {
      const validationErrors = res?.data?.errors;
      const errorMessage = res?.data?.message || "Something went wrong";

      if (validationErrors) {
        const allErrors = Object.entries(validationErrors)
          .map(([field, msgs]) => `${field}: ${(msgs as string[]).join(", ")}`)
          .join("\n");

        toast.error(`❌ Validation Failed:\n${allErrors}`);
      } else {
        toast.error(`❌ Error: ${errorMessage}`);
      }
      return;
    }

    if (!existingId && res?.student_id) {
      setStudentId(res.student_id);
    }

    setFormData((prev: any) => ({
      ...prev,
      personalInfo: finalPayload,
    }));

    setCurrentStep(2);
  } catch (error: any) {
    const response = error?.response;
    if (response?.status === 422 && response?.data?.errors) {
      const validationErrors = response.data.errors;
      const allErrors = Object.entries(validationErrors)
        .map(([field, msgs]) =>
          Array.isArray(msgs) ? `${field}: ${msgs.join(", ")}` : `${field}: ${msgs}`
        )
        .join("\n");

      toast.error(`❌ Validation Failed:\n${allErrors}`);
    } else {
      toast.error("❌ Server Error. Please try again later.");
    }
  }
};





const handleNextStep = async () => {
if (currentStep === 1) {
  const finalData = {
    ...personalInfo,
    languagesKnown: owner, // ✅ Make sure this key name matches your backend
  };
 // ✅ Only validate fields that are required in your Step 1 form
  const requiredFields: (keyof typeof finalData)[] = [
    "firstName",
    "lastName",
    "email",

    "gender",
    "languagesKnown",
    // 👉 Do NOT include "admissionNo", "status", "section" if they're not on the form
  ];

  const emptyFields: string[] = [];

  requiredFields.forEach((key) => {
    const value = finalData[key];

    if (
      value === null ||
      value === undefined ||
      value === "" ||
      (Array.isArray(value) && value.length === 0)
    ) {
      emptyFields.push(key);
    }
  });

  if (emptyFields.length > 0) {
    toast.error(
      `Please fill in the following fields: ${emptyFields
        .map((field) => field.replace(/([A-Z])/g, " $1")) // convert camelCase to readable
        .join(", ")}`
    );
    return;
  }

  const formData = buildFormDataFromPayload(finalData);

  console.log("✅ FormData to be sent:");
  formData.forEach((value, key) => {
    console.log(`${key}:`, value);
  });

  try {
    await handleStep1Submit(
      formData,
      studentId,
      routeStudentId,
      setStudentId,
      setFormData,
      setCurrentStep
      
    );

      // ✅ Optionally show success toast here
 
      toast.success("Step 1 submitted successfully!");
  } catch (error) {
    console.error("❌ Step 1 Submit Error:", error);
    toast.error("❌ Failed to submit Step 1. Please try again.");
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
  } 
catch (error: unknown) {
  console.error("❌ Submit Error:", error);

  let message = "❌ Unknown error occurred.";

  if (
    typeof error === "object" &&
    error !== null &&
    "response" in error &&
    typeof (error as any).response === "object"
  ) {
    const errRes = error as any;
    message =
      errRes.response?.data?.message ||
      errRes.response?.data?.error ||
      errRes.message ||
      message;
  } else if (error instanceof Error) {
    message = error.message;
  }

  toast.error(`❌ Submit Error: ${message}`);
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
        appendFormData(value, formKey);
      } else {
        formData.append(formKey, String(value ?? ""));
      }
    }
  };

  appendFormData(payload);
  formData.append("_method", "PUT");

  try {
    await updateStudent(studentId, formData);
    console.log("✅ Step 3 Payload sent as snake_case FormData");

    toast.success("✅ Address details saved successfully!");

    setFormData((prev) => ({ ...prev, addressInfo: payload }));
    setCurrentStep(4);
  } catch (error: unknown) {
    console.error("❌ Step 3 Submit Error:", error);

    let message = "❌ Unknown error occurred.";

    if (
      typeof error === "object" &&
      error !== null &&
      "response" in error &&
      typeof (error as any).response === "object"
    ) {
      const errRes = error as any;
      message =
        errRes.response?.data?.message ||
        errRes.response?.data?.error ||
        errRes.message ||
        message;
    } else if (error instanceof Error) {
      message = error.message;
    }

    toast.error(`❌ Step 3 Submit Error: ${message}`);
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
    toast.success("✅ Transport & Medical details submitted successfully!");
    setCurrentStep(5);
  } catch (error: unknown) {
    console.error("❌ Error in Step 4 submission", error);

    let message = "❌ Unknown error occurred.";

    if (
      typeof error === "object" &&
      error !== null &&
      "response" in error &&
      typeof (error as any).response === "object"
    ) {
      const errRes = error as any;
      message =
        errRes.response?.data?.message ||
        errRes.response?.data?.error ||
        errRes.message ||
        message;
    } else if (error instanceof Error) {
      message = error.message;
    }

    toast.error(`❌ Step 4 Submit Error: ${message}`);
  }
}

else if (currentStep === 5) {
  const payload = {
    ...documents, // documents from props/state
    step: "step_5",
    studentId,
  };

  const toSnakeCase = (str: string) =>
    str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);

  const formData = new FormData();

  for (const [key, value] of Object.entries(payload)) {
    const snakeKey = toSnakeCase(key);

    if (value instanceof Blob) {
      formData.append(snakeKey, value); // ✅ file upload
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
    toast.success("✅ Documents uploaded successfully!");
    setCurrentStep(6);
  } catch (error: unknown) {
    console.error("❌ Error in Step 5 submission", error);

    let message = "❌ Unknown error occurred.";

    if (
      typeof error === "object" &&
      error !== null &&
      "response" in error &&
      typeof (error as any).response === "object"
    ) {
      const errRes = error as any;
      message =
        errRes.response?.data?.message ||
        errRes.response?.data?.error ||
        errRes.message ||
        message;
    } else if (error instanceof Error) {
      message = error.message;
    }

    toast.error(`❌ Step 5 Submit Error: ${message}`);
  }
}
else if (currentStep === 6) {
  
}
 else {
    // Submit form here
    console.log("Form submitted!");
    // You can trigger a real API call or success state here
  }
};


useEffect(() => {
  if (inquiryConversionMode && inquiryData) {
  


setPersonalInfo((prev) => ({
  ...prev, // ✅ keeps the rest of the required fields intact
  academicYear: inquiryData.academic_year || "",
  firstName: inquiryData.first_name || "",
  middleName: inquiryData.middle_name || "",
  lastName: inquiryData.last_name || "",
class: inquiryData.class_id ? String(inquiryData.class_id) : "",

  gender: inquiryData.gender || "",
  dob: inquiryData.date_of_birth || "",
  primaryContact: inquiryData.primary_contact || "",
  email: inquiryData.email || "",
  suitableBatch: inquiryData.suitable_batch || "",
}));

  }
}, [inquiryConversionMode, inquiryData]);



// ✅ Step wiese fetching data in foms in edit
// Step-2 Data Fetching For Edit Case
useEffect(() => {
   const loadImageAsFile = async (url: string) => {
    const res = await fetch(url);
    const blob = await res.blob();
    const filename = url.split("/").pop() || "profile.jpg";
    return new File([blob], filename, { type: blob.type });
  };
  if (isEditingMode && studentData?.data?.step_1) {
    const s = studentData.data.step_1;

    const updatedInfo = {
      
      academicYear: s.academic_year || "",
      admissionNo: s.admission_no || "",
      admissionDate: s.admission_date || "",
      rollNo: s.roll_no || "",
      status: s.status || "",
      firstName: s.first_name || "",
      middleName: s.middle_name || "",
      lastName: s.last_name || "",
      class: s.class_id || "",
      section: s.section_id || "",
      gender: s.gender || "",
      dob: s.dob || "",
      bloodGroup: s.blood_group || "",
      nationality: s.nationality || "",
      motherTongue: s.mother_tongue || "",
      birthPlace: s.birth_place || "",
      religion: s.religion || "",
      caste: s.caste || "",
      subCaste: s.sub_caste || "",
      category: s.category || "",
      languages: s.languages || [],
      house: s.house || "",
      primaryContact: s.primary_contact || "",
      email: s.email || "",
      suitableBatch: s.suitable_batch || "",
profileImage: null, // will update below

    };

    console.log("Setting personalInfo:", updatedInfo);
    setPersonalInfo(updatedInfo);
setOwner(s.languages || []);
// Load profile image as File
if (s.profile_image) {
  const imageUrl = `${process.env.REACT_APP_BASE_URL}/${s.profile_image}`;

  loadImageAsFile(imageUrl).then((file) => {
    console.log("Fetched image file:", file); // ✅ DEBUG
    setFiles([file]);
    setPersonalInfo((prev) => ({ ...prev, profileImage: file }));
  });
}

  
  }
}, [isEditingMode, studentData]);

// Step-2 Data Fetching For Edit Case
useEffect(() => {
  const loadImageAsFile = async (url: string) => {
    const res = await fetch(url);
    const blob = await res.blob();
    const filename = url.split("/").pop() || "image.jpg";
    return new File([blob], filename, { type: blob.type });
  };

  if (!isEditMode || !studentData?.data?.step_2) return;

  const { father, mother, guardians, sibling_same_school, sibling_student_ids } = studentData.data.step_2;
console.log("studentData.data.step_2 ===>", studentData?.data.step_2);

  const updatedParentInfo = {
    fatherName: father?.name || "",
    fatherPhone: father?.phone || "",
    fatherEmail: father?.email || "",
    fatherAadhar: father?.aadhar || "",
    fatherQualification: father?.qualiffication || "",
    fatherOccupation: father?.occupation || "",
    fatherItrNo: father?.itr_no || "",
    fatherProfileImage: null,
    fatherAadharImage: null,

    motherName: mother?.name || "",
    motherPhone: mother?.phone || "",
    motherEmail: mother?.email || "",
    motherAadhar: mother?.aadhar || "",
    motherQualification: mother?.qualiffication || "",
    motherOccupation: mother?.occupation || "",
    motherItrNo: mother?.itr_no || "",
    motherProfileImage: null,
    motherAadharImage: null,

    siblingSameSchool: sibling_same_school || "",
    siblingStudentIds: sibling_student_ids?.filter(Boolean) || [""],

    guardians: guardians?.length
      ? guardians.map((g: any) => ({
          name: g.name || "",
          phone: g.phone || "",
          aadhar: g.aadhar || "",
          occupation: g.occupation || "",
          relation: g.relation || "",
          profileImage: null,
          aadharImage: null,
        }))
      : [
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
  };

  setParentInfo(updatedParentInfo);

  // Load images
  if (father?.image) {
    const imageUrl = `${process.env.REACT_APP_BASE_URL}/${father.image}`;
    loadImageAsFile(imageUrl).then((file) =>
      setParentInfo((prev) => ({ ...prev, fatherProfileImage: file }))
    );
  }

  if (mother?.image) {
    const imageUrl = `${process.env.REACT_APP_BASE_URL}/${mother.image}`;
    loadImageAsFile(imageUrl).then((file) =>
      setParentInfo((prev) => ({ ...prev, motherProfileImage: file }))
    );
  }
}, [isEditMode, studentData]);


// Step-3 Data Fetching For Edit Case
useEffect(() => {
  if (!isEditMode || !studentData?.data?.step_3) return;

  console.log("studentData.data.step_3 ===>", studentData.data.step_3);

  const { permanent_address, current_address } = studentData.data.step_3;

  const updatedAddressInfo = {
    permanent: {
      address: permanent_address?.address || "",
      area: permanent_address?.area || "",
      landmark: permanent_address?.landmark || "",
      city: permanent_address?.city || "",
      state: permanent_address?.state || "",
      pincode: permanent_address?.pincode || "",
    },
    current: {
      address: current_address?.address || "",
      area: current_address?.area || "",
      landmark: current_address?.landmark || "",
      city: current_address?.city || "",
      state: current_address?.state || "",
      pincode: current_address?.pincode || "",
    },
  };

  setAddressInfo(updatedAddressInfo);
}, [isEditMode, studentData]);

useEffect(() => {
  if (!isEditMode || !studentData?.data?.step_4) return;

  console.log("studentData.data.step_4 ===>", studentData.data.step_4);

  const {
    transport_service,
    serious_disease,
    serious_injuries,
    allergies,
    medications,
    previous_school_name,
    previous_school_address,
  } = studentData.data.step_4;

  const updatedTransportMedical = {
    transportService: transport_service || "",
    seriousDisease: serious_disease || "",
    seriousInjuries: serious_injuries?.filter(Boolean) || [],
    allergies: allergies?.filter(Boolean) || [],
    medications: medications?.filter(Boolean) || [],
    previousSchoolName: previous_school_name || "",
    previousSchoolAddress: previous_school_address || "",
  };

  setTransportMedical(updatedTransportMedical);
}, [isEditMode, studentData]);
useEffect(() => {
  if (id) {
    getInquiryById(id)
      .then((data) => {
        setStudentData(data.data); // ✅ now `data` is from response.data
      })
      .catch((err) => {
        console.error("API error:", err);
        setStudentData(null);
      })
      .finally(() => setLoading(false));
  }
}, [id]);

const [serialId, setSerialId] = useState<string>("");


useEffect(() => {
  const loadId = async () => {
    const id = await getAllId("student", StudentEditCode);
    console.log(id)
    setSerialId(id); // ✅ This is correct
  };

  loadId();
}, [studentData]);

  let  gotclassId = personalInfo.class
// if (gotclassId){toast.success(gotclassId)}else{
//   toast.error("opps")
// }
  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">


        <div className="content content-two">

  <>
          {/* Page Header */}
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
<div className="d-flex align-items-center mb-2">
<h3 className="mb-0 me-2">
  {isEdit
    ? 'Edit'
    : inquiryConversionMode
    ? 'Convert Inquiry to Admission'
    : 'Add Student'}
</h3>

    <div
      className="px-2 py-1"
      style={{
        fontSize: "0.75rem",
        color: "#333",
        backgroundColor: "#E6F0FA",
        borderRadius: "6px",
        width: "fit-content",
      }}
    >
<div>
{serialId? (
  <p>student code: {serialId}</p>
) : (
  <p>Loading...</p>
)}

</div>

    </div>
 
</div>

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
<MultiStepProgressBar
  currentStep={currentStep}
  steps={steps}
  onStepClick={(step) => setCurrentStep(step)}
  isEditMode={isEditMode}
  studentId={studentId} // optional now
/>




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
  // ✅ new prop
  studentId={studentId} 
    isEditMode={isEditMode}
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

{currentStep === 6 && (<FinancialSummary
gotClassId={gotclassId}
  studentName="Kapil Raj Desai"
  studentCode={serialId}
  course="BCA"
  admissionDate="2025-06-15"
    currentStep={currentStep}
  setCurrentStep={setCurrentStep}

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
    <button
      className="btn btn-outline-dark d-flex align-items-center gap-2 px-4 py-2 rounded shadow-sm"
      onClick={handleBack}
    >
      <span className="fw-medium">Back</span>
    </button>
  )}

  <button
    className={`btn d-flex align-items-center gap-2 px-4 py-2 rounded shadow-sm ${
      currentStep === 6 ? "bg-btn-primary text-white" : "btn-primary text-white"
    }`}
    onClick={handleNextStep}
  >
    <span className="fw-medium">
      {currentStep === 6 ? "Accept & Submit" : "Next Step"}
    </span>
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
