import React, {
  forwardRef,
  useImperativeHandle,
  useState,
  useEffect
  
} from "react";
import { useParams } from "react-router-dom";
import axios from "axios";
import { DatePicker } from "antd";
import dayjs from "dayjs";
import { Link } from "react-router-dom";
import { TagsInput } from "react-tag-input-component";
import CommonSelect from "../../../../core/common/commonSelect";
import {
  academicYear,
  status,
  bloodGroup,
  house,
  religion,
  cast,
  gender,
  mothertongue,
  allSection,
  Class,
} from "../../../../core/common/selectoption/selectoption";
import { PersonalInfoType } from ".";
import FileUploader from "../../../../utils/FileUploader";

interface Props {
  personalInfo: any;
  setPersonalInfo: (val: any) => void;
  classOptions: { label: string; value: string }[];
  owner: string[];
  setOwner: (val: string[]) => void;
    files: File[]; // ✅ Instead of FileList
 setFiles: (val: File[]) => void;

  setFormData?: (val: any) => void; // ✅ Add this line
  currentStep?: number; // Optional, if you need to track the step
  
  isEditMode?: boolean;
  studentId?: string;
}


export interface PersonalInfoFormRef {
  validateAndGetData: () => null | { step: string; [key: string]: any };
}

const PersonalInfoForm = forwardRef<PersonalInfoFormRef, Props>(
  (
    {
      personalInfo,
      setPersonalInfo,
      
      owner,
      setOwner,
      setFiles,
      studentId 
       
    },
    ref
  ) => {
    const [errors, setErrors] = useState<{ [key: string]: boolean }>({});

    const requiredFields = [
      "academicYear",
      "admissionNo",
      "admissionDate",
      "rollNo",
      "status",
      "firstName",
     
      "section",
      "gender",
      "dob",
    ];

    useImperativeHandle(ref, () => ({
      validateAndGetData() {
        const newErrors: { [key: string]: boolean } = {};
        requiredFields.forEach((field) => {
          if (!personalInfo[field]) {
            newErrors[field] = true;
          }
        });
        setErrors(newErrors);

        if (Object.keys(newErrors).length > 0) return null;

        return {
          step: "personalInfo", // helpful identifier
          ...personalInfo,
          languages: owner,
        };
      },
    }));

useEffect(() => {
  if (studentId && personalInfo?.firstName === "") {
    const fetchData = async () => {
      try {
        const response = await axios.get(`/api/students/${studentId}`);
        const data = response.data;

        setPersonalInfo({
          ...personalInfo,
          ...data,
        });

        if (data.profileImage) {
          setFiles([
            {
              name: "Profile Image",
              type: "image/jpeg",
              size: 12345,
              lastModified: new Date().getTime(),
              ...data.profileImage,
            },
          ]);
        }
      } catch (err) {
        console.error("Error fetching student data:", err);
      }
    };

    fetchData();
  }
}, [studentId]);
console.log("BASE_URL:", process.env.REACT_APP_BASE_URL);


return (
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
<FileUploader
  file={personalInfo.profileImage instanceof File ? personalInfo.profileImage : null}
  imageUrl={
    typeof personalInfo.profileImage === "string"
      ? personalInfo.profileImage
      : undefined
  }
  fileTypes="image/*"
  previewType="image"
  onFileChange={(file) => {
    setPersonalInfo((prev: PersonalInfoType) => ({
      ...prev,
      profileImage: file,
    }));
  }}
/>

        </div>
      </div>

      <div className="row row-cols-xxl-5 row-cols-md-6">
        {[
          { label: "Academic Year", type: "select", key: "academicYear", options: academicYear },
          // { label: "Admission Number", type: "text", key: "admissionNo" },
          { label: "Admission Date", type: "date", key: "admissionDate" },
          { label: "Roll Number", type: "text", key: "rollNo" },
          // { label: "Status", type: "select", key: "status", options: status },
          { label: "First Name", type: "text", key: "firstName" },
          { label: "Middle Name", type: "text", key: "middleName" },
          { label: "Last Name", type: "text", key: "lastName" },
                    { label: "Email Address", type: "text", key: "email" },
          { label: "Class", type: "select", key: "class", options: Class },
          // { label: "Section", type: "select", key: "section", options: allSection },
          { label: "Gender", type: "select", key: "gender", options: gender },
          { label: "Date of Birth", type: "date", key: "dob" },
          { label: "Blood Group", type: "select", key: "bloodGroup", options: bloodGroup },
          { label: "House", type: "select", key: "house", options: house },
          { label: "Religion", type: "select", key: "religion", options: religion },
           { label: "Caste", type: "text", key: "caste" },
          { label: "Category", type: "select", key: "category", options: cast },
          { label: "Primary Contact Number", type: "text", key: "primaryContact" },

         
          { label: "Suitable Batch", type: "select", key: "suitableBatch", options: mothertongue },
        ].map((field, idx) => (
          <div className="col-xxl col-xl-3 col-md-6" key={idx}>
            <div className="mb-3">
              <label className="form-label">{field.label}</label>
              {field.type === "text" && (
                <input
                  type="text"
                  className={`form-control ${errors[field.key] ? "border border-danger" : ""}`}
                  value={personalInfo[field.key] || ""}
                  onChange={(e) =>
                    setPersonalInfo({ ...personalInfo, [field.key]: e.target.value })
                  }
                />
              )}
              {field.type === "select" && (
                <CommonSelect
                  className={`select ${errors[field.key] ? "border border-danger" : ""}`}
                  options={field.options || []}
                  value={field.options?.find((o: any) => o.value === personalInfo[field.key])}
                  onChange={(option) =>
                    setPersonalInfo({ ...personalInfo, [field.key]: option?.value || "" })
                  }
                />
              )}
              {field.type === "date" && (
                <div className="input-icon position-relative">
                  <DatePicker
                    value={personalInfo[field.key] ? dayjs(personalInfo[field.key]) : null}
                    onChange={(date) =>
                      setPersonalInfo({
                        ...personalInfo,
                        [field.key]: dayjs(date).format("YYYY-MM-DD"),
                      })
                    }
                  />
                  <span className="input-icon-addon">
                    <i className="ti ti-calendar" />
                  </span>
                </div>
              )}
            </div>
          </div>
        ))}

        {/* Language Known */}
        <div className="col-xxl col-xl-3 col-md-6">
          <div className="mb-3">
            <label className="form-label">Language Known</label>
<TagsInput
  value={owner}
  onChange={(val) => {
    console.log("Selected languages:", val); // ✅ check if val has values
    setOwner(val);
    setPersonalInfo((prev: any) => ({ ...prev, languages: val }));
  }}
/>


          </div>
        </div>
      </div>
    </div>
  </div>
);

  }
);

export default PersonalInfoForm;
