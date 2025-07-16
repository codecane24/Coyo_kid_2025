import React, { useState } from "react";
import { Link } from "react-router-dom";
import CommonSelect from "../../../../core/common/commonSelect";
interface Props {
  currentStep: number;
  setCurrentStep: React.Dispatch<React.SetStateAction<number>>;
  setFormData: React.Dispatch<React.SetStateAction<any>>;
  parentInfo: {
    fatherName: string;
    fatherPhone: string;
    fatherAdhar: string;
    fatherOccupation: string;
    motherName: string;
    motherPhone: string;
    motherAdhar: string;
    motherOccupation: string;
    siblingSameSchool: string;
  };
  setParentInfo: React.Dispatch<
    React.SetStateAction<{
      fatherName: string;
      fatherPhone: string;
      fatherAdhar: string;
      fatherOccupation: string;
      motherName: string;
      motherPhone: string;
      motherAdhar: string;
      motherOccupation: string;
      siblingSameSchool: string;
    }>
  >;
  isEdit: boolean;
  newContents: any[];
  addNewContent: () => void;
  removeContent: (index: number) => void;
  allClass: { label: string; value: string }[];
  names: { label: string; value: string }[];
  rollno: { label: string; value: string }[];
  AdmissionNo: { label: string; value: string }[];
}


const ParentsGuardianForm: React.FC<Props> = ({
  currentStep,
  setCurrentStep,
  setFormData,
  isEdit,
  newContents,
  addNewContent,
  removeContent,
  allClass,
  names,
  rollno,
  AdmissionNo,
}) => {
const [parentInfo, setParentInfo] = useState<{
  [key: string]: string;
  fatherName: string;
  fatherPhone: string;
  fatherAdhar: string;
  fatherOccupation: string;
  motherName: string;
  motherPhone: string;
  motherAdhar: string;
  motherOccupation: string;
  siblingSameSchool: string;
}>({
  fatherName: "",
  fatherPhone: "",
  fatherAdhar: "",
  fatherOccupation: "",
  motherName: "",
  motherPhone: "",
  motherAdhar: "",
  motherOccupation: "",
  siblingSameSchool: "yes",
});


  const [errors, setErrors] = useState<{ [key: string]: boolean }>({});

  const handleValidation = () => {
    const requiredFields = [
      "fatherName",
      "fatherPhone",
      "fatherAdhar",
      "fatherOccupation",
      "motherName",
      "motherPhone",
      "motherAdhar",
      "motherOccupation",
    ];

    const newErrors: { [key: string]: boolean } = {};
    requiredFields.forEach((field) => {
      if (!parentInfo[field]) {
        newErrors[field] = true;
      }
    });

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // To be called by parent on Next button click
  const handleNext = () => {
    if (!handleValidation()) return;

    const payload = {
      ...parentInfo,
      siblings: newContents,
    };

    console.log("Payload: Parents & Guardian Info", payload);

    setFormData((prev: any) => ({
      ...prev,
      parentsGuardianInfo: payload,
    }));

    setCurrentStep(currentStep + 1);
  };

  // Expose handleNext to parent if needed via ref later

  return (
    <>
      <div className="card">
        <div className="card-header bg-light">
          <div className="d-flex align-items-center">
            <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
              <i className="ti ti-user-shield fs-16" />
            </span>
            <h4 className="text-dark">Parents & Guardian Information</h4>
          </div>
        </div>
        <div className="card-body pb-0">
          <div className="border-bottom mb-3">
            <h5 className="mb-3">Father’s Info</h5>
            <div className="row">
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Father Name</label>
                <input
                  type="text"
                  className={`form-control ${errors.fatherName ? "border-danger" : ""}`}
                  value={parentInfo.fatherName}
                  onChange={(e) => setParentInfo({ ...parentInfo, fatherName: e.target.value })}
                />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Phone Number</label>
                <input
                  type="text"
                  className={`form-control ${errors.fatherPhone ? "border-danger" : ""}`}
                  value={parentInfo.fatherPhone}
                  onChange={(e) => setParentInfo({ ...parentInfo, fatherPhone: e.target.value })}
                />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Adhar Number</label>
                <input
                  type="text"
                  className={`form-control ${errors.fatherAdhar ? "border-danger" : ""}`}
                  value={parentInfo.fatherAdhar}
                  onChange={(e) => setParentInfo({ ...parentInfo, fatherAdhar: e.target.value })}
                />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Father Occupation</label>
                <input
                  type="text"
                  className={`form-control ${errors.fatherOccupation ? "border-danger" : ""}`}
                  value={parentInfo.fatherOccupation}
                  onChange={(e) => setParentInfo({ ...parentInfo, fatherOccupation: e.target.value })}
                />
              </div>
            </div>
          </div>

          <div className="border-bottom mb-3">
            <h5 className="mb-3">Mother’s Info</h5>
            <div className="row">
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Mother Name</label>
                <input
                  type="text"
                  className={`form-control ${errors.motherName ? "border-danger" : ""}`}
                  value={parentInfo.motherName}
                  onChange={(e) => setParentInfo({ ...parentInfo, motherName: e.target.value })}
                />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Phone Number</label>
                <input
                  type="text"
                  className={`form-control ${errors.motherPhone ? "border-danger" : ""}`}
                  value={parentInfo.motherPhone}
                  onChange={(e) => setParentInfo({ ...parentInfo, motherPhone: e.target.value })}
                />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Adhar Number</label>
                <input
                  type="text"
                  className={`form-control ${errors.motherAdhar ? "border-danger" : ""}`}
                  value={parentInfo.motherAdhar}
                  onChange={(e) => setParentInfo({ ...parentInfo, motherAdhar: e.target.value })}
                />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Mother Occupation</label>
                <input
                  type="text"
                  className={`form-control ${errors.motherOccupation ? "border-danger" : ""}`}
                  value={parentInfo.motherOccupation}
                  onChange={(e) => setParentInfo({ ...parentInfo, motherOccupation: e.target.value })}
                />
              </div>
            </div>
          </div>

          <div className="mb-3">
            <label className="form-label">Is sibling studying in the same school?</label>
            <div className="d-flex gap-4">
              <label className="form-check-label">
                <input
                  type="radio"
                  name="sibling"
                  value="yes"
                  checked={parentInfo.siblingSameSchool === "yes"}
                  onChange={() => setParentInfo({ ...parentInfo, siblingSameSchool: "yes" })}
                />
                Yes
              </label>
              <label className="form-check-label">
                <input
                  type="radio"
                  name="sibling"
                  value="no"
                  checked={parentInfo.siblingSameSchool === "no"}
                  onChange={() => setParentInfo({ ...parentInfo, siblingSameSchool: "no" })}
                />
                No
              </label>
            </div>
          </div>
        </div>
      </div>

      {/* Siblings */}
      <div className="card">
        <div className="card-header bg-light">
          <div className="d-flex align-items-center">
            <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
              <i className="ti ti-users fs-16" />
            </span>
            <h4 className="text-dark">Siblings</h4>
          </div>
        </div>
        <div className="card-body">
          {newContents.map((_, index) => (
            <div key={index} className="row">
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Name</label>
                <CommonSelect className="select" options={names} />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Class</label>
                <CommonSelect className="select" options={allClass} />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Section</label>
                <CommonSelect className="select" options={allClass} />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Roll No</label>
                <CommonSelect className="select" options={rollno} />
              </div>
              <div className="col-lg-3 col-md-6 mb-3">
                <label className="form-label">Admission No</label>
                <CommonSelect className="select" options={AdmissionNo} />
              </div>
              <div className="col-lg-1 col-md-6 d-flex align-items-center mb-3">
                {newContents.length > 1 && (
                  <Link to="#" onClick={() => removeContent(index)}>
                    <i className="ti ti-trash-x fs-18 text-danger" />
                  </Link>
                )}
              </div>
            </div>
          ))}

          <div className="pt-2">
            <Link to="#" onClick={addNewContent} className="btn btn-primary">
              <i className="ti ti-circle-plus me-2" />
              Add New Sibling
            </Link>
          </div>
        </div>
      </div>
    </>
  );
};

export default ParentsGuardianForm;
