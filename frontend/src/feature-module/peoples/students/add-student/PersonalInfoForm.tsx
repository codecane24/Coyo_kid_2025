import React, {
  forwardRef,
  useImperativeHandle,
  useState,
} from "react";
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
} from "../../../../core/common/selectoption/selectoption";

interface Props {
  personalInfo: any;
  setPersonalInfo: (val: any) => void;
  classOptions: { label: string; value: string }[];
  owner: string[];
  setOwner: (val: string[]) => void;
  files: FileList | null;
  setFiles: (val: FileList | null) => void;
  setFormData?: (val: any) => void; // ✅ Add this line
}


export interface PersonalInfoFormRef {
  validateAndGetData: () => null | { step: string; [key: string]: any };
}

const PersonalInfoForm = forwardRef<PersonalInfoFormRef, Props>(
  (
    {
      personalInfo,
      setPersonalInfo,
      classOptions,
      owner,
      setOwner,
      files,
      setFiles,
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
      "class",
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
            {[
              { label: "Academic Year", type: "select", key: "academicYear", options: academicYear },
              { label: "Admission Number", type: "text", key: "admissionNo" },
              { label: "Admission Date", type: "date", key: "admissionDate" },
              { label: "Roll Number", type: "text", key: "rollNo" },
              { label: "Status", type: "select", key: "status", options: status },
              { label: "First Name", type: "text", key: "firstName" },
              { label: "Middle Name", type: "text", key: "middleName" },
              { label: "Last Name", type: "text", key: "lastName" },
              { label: "Class", type: "select", key: "class", options: classOptions },
              { label: "Section", type: "select", key: "section", options: allSection },
              { label: "Gender", type: "select", key: "gender", options: gender },
              { label: "Date of Birth", type: "date", key: "dob" },
              { label: "Blood Group", type: "select", key: "bloodGroup", options: bloodGroup },
              { label: "House", type: "select", key: "house", options: house },
              { label: "Religion", type: "select", key: "religion", options: religion },
              { label: "Category", type: "select", key: "category", options: cast },
              { label: "Primary Contact Number", type: "text", key: "primaryContact" },
              { label: "Email Address", type: "text", key: "email" },
              { label: "Caste", type: "text", key: "caste" },
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
                      value={field.options?.find(
                        (o: any) => o.value === personalInfo[field.key]
                      )}
                      onChange={(option) =>
                        setPersonalInfo({ ...personalInfo, [field.key]: option?.value || "" })
                      }
                    />
                  )}
                  {field.type === "date" && (
                    <div className="input-icon position-relative">
                      <DatePicker
                        value={dayjs(personalInfo[field.key] || new Date())}
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
                <TagsInput value={owner} onChange={(val) => setOwner(val)} />
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
);

export default PersonalInfoForm;
