import React from "react";
import { Link } from "react-router-dom";

const siblingOptions = ["STD2123", "STD4566", "STD7890"];


interface Guardian {
  name: string;
  phone: string;
  aadhar: string;
  occupation: string;
  relation: string;
  profileImage?: File | null;
  aadharImage?: File | null;
}

export interface ParentInfo {
  fatherName: string;
  fatherPhone: string;
    fatherEmail?: string; 
  fatherAadhar: string;
  fatherOccupation: string;
  fatherProfileImage?: File | null;
  fatherAadharImage?: File | null;

  motherName: string;
    motherEmail?: string;
  motherPhone: string;
  motherAadhar: string;
  motherOccupation: string;
  motherProfileImage?: File | null;
  motherAadharImage?: File | null;

  siblingSameSchool: string;

  siblingStudentIds: string[]; 
  guardians: Guardian[];
}

interface Props {
  parentInfo: ParentInfo;
  setParentInfo: React.Dispatch<React.SetStateAction<ParentInfo>>;
  currentStep: number;
}

const ParentsGuardianForm: React.FC<Props> = ({ parentInfo, setParentInfo }) => {
  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setParentInfo((prev) => ({ ...prev, [name]: value }));
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>, field: keyof ParentInfo) => {
    const file = e.target.files?.[0] || null;
    setParentInfo((prev) => ({ ...prev, [field]: file }));
  };

  const handleGuardianChange = (index: number, field: keyof Guardian, value: any) => {
    const updated = [...parentInfo.guardians];
    updated[index][field] = value;
    setParentInfo({ ...parentInfo, guardians: updated });
  };

  const handleGuardianImage = (index: number, field: keyof Guardian, file: File | null) => {
    const updated = [...parentInfo.guardians];
    updated[index] = {
      ...updated[index],
      [field]: file,
    };
    setParentInfo({ ...parentInfo, guardians: updated });
  };

  const addGuardian = () => {
    setParentInfo((prev) => ({
      ...prev,
      guardians: [
        ...prev.guardians,
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
    }));
  };

  const removeGuardian = (index: number) => {
    const updated = [...parentInfo.guardians];
    updated.splice(index, 1);
    setParentInfo({ ...parentInfo, guardians: updated });
  };
const handleSiblingChange = (value: string, index: number) => {
  const updatedSiblings = [...parentInfo.siblingStudentIds];
  updatedSiblings[index] = value;
  setParentInfo((prev) => ({ ...prev, siblingStudentIds: updatedSiblings })); // ✅ corrected
};



const addSibling = () => {
  setParentInfo((prev) => ({ ...prev, siblingStudentIds: [...prev.siblingStudentIds, ""] })); // ✅ corrected
};


const removeSibling = (index: number) => {
  const updatedSiblings = [...parentInfo.siblingStudentIds];
  updatedSiblings.splice(index, 1);
  setParentInfo((prev) => ({ ...prev, siblingStudentIds: updatedSiblings })); // ✅ corrected
};

const renderImageUploader = (
  label: string,
  field: keyof ParentInfo,
  removeFn: () => void,
  accept: string = "image/*" // default is images only
) => (
  <div className="file-upload mb-2">
    <label className="block text-sm font-medium mb-1">{label}</label>
    <div className="d-flex align-items-center">
      <div className="drag-upload-btn mb-2">
        Upload
    <input
  type="file"
  className="form-control image-sign"
  accept={accept} // ✅ now dynamic
  onChange={(e) => handleImageChange(e, field)}
/>

      </div>
      <Link to="#" className="btn btn-primary mb-2" onClick={removeFn}>
        Remove
      </Link>
    </div>
    <p className="fs-12 text-sm text-gray-500">
      Max 4MB. Allowed: JPG, PNG{accept.includes("pdf") && ", PDF"}
    </p>
  </div>
);
  return (
<div className="container-fluid py-3">
  
  {/* FATHER */}
  <div className="card mb-5">
    <div className="card-header bg-light">
      <div className="d-flex align-items-center">
        <span className="bg-white avatar avatar-sm me-2 text-gray-7 d-flex justify-content-center align-items-center">
          <i className="ti ti-user fs-16" />
        </span>
        <h4 className="text-dark mb-0">Father's Details</h4>
      </div>
    </div>
    <div className="card-body pb-1">
      <div className="row row-cols-xxl-5 row-cols-md-6">
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Father Name</label>
          <input type="text" name="fatherName" value={parentInfo.fatherName} onChange={handleInputChange} className="form-control" placeholder="Enter Name" />
        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
  <label className="form-label">Email</label>
  <input
    type="email"
    name="fatherEmail"
    value={parentInfo.fatherEmail || ""}
    onChange={handleInputChange}
    className="form-control"
    placeholder="Enter Email"
  />
</div>

        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Phone</label>
          <input type="text" name="fatherPhone" value={parentInfo.fatherPhone} onChange={handleInputChange} className="form-control" placeholder="Enter Phone" />
        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Aadhar</label>
          <input type="text" name="fatherAadhar" value={parentInfo.fatherAadhar} onChange={handleInputChange} className="form-control" placeholder="Enter Aadhar" />
        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Occupation</label>
          <input type="text" name="fatherOccupation" value={parentInfo.fatherOccupation} onChange={handleInputChange} className="form-control" placeholder="Enter Occupation" />
        </div>
    {/* Only Image — Father's Profile Image */}
<div className="col-xxl col-xl-3 col-md-6 mb-3">
  {renderImageUploader(
    "Father's Profile Image",
    "fatherProfileImage",
    () =>
      handleImageChange({ target: { files: null } } as any, "fatherProfileImage"),
    "image/*" // ✅ Only allow images
  )}
</div>

{/* Image + PDF — Father's Aadhar Image */}
<div className="col-xxl col-xl-3 col-md-6 mb-3">
  {renderImageUploader(
    "Father's Aadhar Image",
    "fatherAadharImage",
    () =>
      handleImageChange({ target: { files: null } } as any, "fatherAadharImage"),
    "image/*,.pdf" // ✅ Allow images and PDFs
  )}
</div>

      </div>
    </div>
  </div>

  {/* MOTHER */}
  <div className="card mb-5">
    <div className="card-header bg-light">
      <div className="d-flex align-items-center">
        <span className="bg-white avatar avatar-sm me-2 text-gray-7 d-flex justify-content-center align-items-center">
          <i className="ti ti-user fs-16" />
        </span>
        <h4 className="text-dark mb-0">Mother's Details</h4>
      </div>
    </div>
    <div className="card-body pb-1">
      <div className="row row-cols-xxl-5 row-cols-md-6">
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Mother Name</label>
          <input type="text" name="motherName" value={parentInfo.motherName} onChange={handleInputChange} className="form-control" placeholder="Enter Name" />
        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Phone</label>
          <input type="text" name="motherPhone" value={parentInfo.motherPhone} onChange={handleInputChange} className="form-control" placeholder="Enter Phone" />
        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
  <label className="form-label">Email</label>
  <input
    type="email"
    name="motherEmail"
    value={parentInfo.motherEmail || ""}
    onChange={handleInputChange}
    className="form-control"
    placeholder="Enter Email"
  />
</div>

        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Aadhar</label>
          <input type="text" name="motherAadhar" value={parentInfo.motherAadhar} onChange={handleInputChange} className="form-control" placeholder="Enter Aadhar" />
        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
          <label className="form-label">Occupation</label>
          <input type="text" name="motherOccupation" value={parentInfo.motherOccupation} onChange={handleInputChange} className="form-control" placeholder="Enter Occupation" />
        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
       {renderImageUploader(
  "Mother's Profile Image",
  "motherProfileImage",
  () => handleImageChange({ target: { files: null } } as any, "motherProfileImage"),
  "image/*" // ← only image
)}

        </div>
        <div className="col-xxl col-xl-3 col-md-6 mb-3">
 {renderImageUploader(
  "Mother's Aaadhar Image",
  "motherAadharImage",
  () => handleImageChange({ target: { files: null } } as any, "motherAadharImage"),
  "image/*,.pdf" // ← allow both image & PDF
)}

        </div>
      </div>
    </div>
  </div>
{/* GUARDIANS */}
<div className="card mb-5">
  <div className="card-header bg-light">
    <div className="d-flex align-items-center">
      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0 d-flex justify-content-center align-items-center">
        <i className="ti ti-users fs-16" />
      </span>
      <h4 className="text-dark mb-0">Guardian's Information</h4>
    </div>
  </div>

  <div className="card-body pb-1">
    {parentInfo.guardians.map((guardian, index) => (
      <div key={index} className="border border-dashed rounded p-3 mb-4 bg-light-subtle">
        <div className="row row-cols-xxl-5 row-cols-md-6">
          {/* Name */}
          <div className="col-xxl col-xl-3 col-md-6">
            <div className="mb-3">
              <label className="form-label">Name</label>
              <input
                type="text"
                className="form-control"
                value={guardian.name}
                onChange={(e) => handleGuardianChange(index, "name", e.target.value)}
                placeholder="Enter Name"
              />
            </div>
          </div>

          {/* Phone */}
          <div className="col-xxl col-xl-3 col-md-6">
            <div className="mb-3">
              <label className="form-label">Phone</label>
              <input
                type="text"
                className="form-control"
                value={guardian.phone}
                onChange={(e) => handleGuardianChange(index, "phone", e.target.value)}
                placeholder="Enter Phone"
              />
            </div>
          </div>

          {/* Aadhar */}
          <div className="col-xxl col-xl-3 col-md-6">
            <div className="mb-3">
              <label className="form-label">Aadhar</label>
              <input
                type="text"
                className="form-control"
                value={guardian.aadhar}
                onChange={(e) => handleGuardianChange(index, "aadhar", e.target.value)}
                placeholder="Enter Aadhar"
              />
            </div>
          </div>

          {/* Occupation */}
          <div className="col-xxl col-xl-3 col-md-6">
            <div className="mb-3">
              <label className="form-label">Occupation</label>
              <input
                type="text"
                className="form-control"
                value={guardian.occupation}
                onChange={(e) => handleGuardianChange(index, "occupation", e.target.value)}
                placeholder="Enter Occupation"
              />
            </div>
          </div>

          {/* Relation */}
          <div className="col-xxl col-xl-3 col-md-6">
            <div className="mb-3">
              <label className="form-label">Relation</label>
              <input
                type="text"
                className="form-control"
                value={guardian.relation}
                onChange={(e) => handleGuardianChange(index, "relation", e.target.value)}
                placeholder="Enter Relation"
              />
            </div>
          </div>

          {/* Profile Image */}
          <div className="col-xxl col-xl-3 col-md-6">
            <div className="mb-3">
              <label className="form-label">Profile Image</label>
          <input
  type="file"
  accept="image/*"
  className="form-control"
  onChange={(e) =>
    handleGuardianImage(index, "profileImage", e.target.files?.[0] || null)
  }
/>

            </div>
          </div>

          {/* Aadhar Image */}
          <div className="col-xxl col-xl-3 col-md-6">
            <div className="mb-3">
              <label className="form-label">Aadhar Image</label>
              <input
                type="file"
                className="form-control"
                onChange={(e) =>
                  handleGuardianImage(index, "aadharImage", e.target.files?.[0] || null)
                }
              />
            </div>
          </div>
        </div>

        {/* Remove Button */}
        <div className="mt-2">
          <button
            type="button"
            className="btn btn-outline-danger btn-sm"
            onClick={() => removeGuardian(index)}
          >
            Remove Guardian
          </button>
        </div>
      </div>
    ))}

    {/* Add Guardian Button */}
    <button type="button" onClick={addGuardian} className="btn btn-primary">
      Add More Guardian
    </button>
  </div>
</div>


{/* SIBLING */}
{/* SIBLING */}
{/* SIBLING INFO */}
<div className="card mb-5">
  <div className="card-header bg-light">
    <div className="d-flex align-items-center">
      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0 d-flex justify-content-center align-items-center">
        <i className="ti ti-users-group fs-16" />
      </span>
      <h4 className="text-dark mb-0">Sibling Information</h4>
    </div>
  </div>

  <div className="card-body pb-1">
    <div className="row row-cols-xxl-5 row-cols-md-6">
      {/* Sibling in Same School */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Is any sibling in the same school?</label>
          <div className="d-flex gap-3">
  <div className="form-check">
    <input
      className="form-check-input"
      type="radio"
      name="siblingSameSchool"
      value="yes"
      checked={parentInfo.siblingSameSchool === "yes"}
      onChange={(e) =>
        setParentInfo((prev) => ({
          ...prev,
          siblingSameSchool: e.target.value,
        }))
      }
    />
    <label className="form-check-label">Yes</label>
  </div>
  <div className="form-check">
    <input
      className="form-check-input"
      type="radio"
      name="siblingSameSchool"
      value="no"
      checked={parentInfo.siblingSameSchool === "no"}
      onChange={(e) =>
        setParentInfo((prev) => ({
          ...prev,
          siblingSameSchool: e.target.value,
        }))
      }
    />
    <label className="form-check-label">No</label>
  </div>
</div>
      {parentInfo.siblingSameSchool === "yes" && (
  <div className="col-12">
    <label className="form-label">Sibling Student ID(s)</label>
{parentInfo.siblingStudentIds.map((siblingId: string, index: number) => (
  <div key={index} className="d-flex align-items-center gap-2 mb-2">
    <select
      value={siblingId}
      onChange={(e) => handleSiblingChange(e.target.value, index)}
      className="form-select w-auto"
    >
      <option value="">Select ID</option>
      {siblingOptions.map((id) => (
        <option key={id} value={id}>
          {id}
        </option>
      ))}
    </select>
    <button
      type="button"
      className="btn btn-sm btn-danger"
      onClick={() => removeSibling(index)}
    >
      Remove
    </button>
  </div>
))}


    <button
      type="button"
      className="btn btn-sm btn-outline-primary mt-2"
      onClick={addSibling}
    >
      + Add Sibling
    </button>
  </div>
)}
     </div>
      </div>
  </div>
  </div>
</div>

</div>


  );
};

export default ParentsGuardianForm;
