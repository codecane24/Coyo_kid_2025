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
  suitableBatch,
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
import { preparePayload } from "../../../../utils/preparePayload";
import axiosInstance from "../../../../utils/axiosInstance";
interface Address {
  colony: string;
  area: string;
  landmark: string;
  city: string;
  state: string;
  pincode: string;
}

interface InquiryFormData {
  academicYear: string;
  dateOfEnquiry: string; // store in ISO string or formatted string depending on your backend
  firstName: string;
  middleName: string;
  lastName: string;
  selectedClass: string;
  gender: string;
  dateOfBirth: string;
  primaryContact: string;
  email: string;
  suitableBatch: string;
    // Father's details
  fatherName: string;
  fatherEmail: string;
  fatherPhone: string;
  fatherAadhar: string;
  fatherOccupation: string;
  fatherProfileImage: File | null;
  fatherAadharImage: File | null;

  // Mother's details
  motherName: string;
  motherPhone: string;
  motherEmail: string;
  motherAadhar: string;
  motherOccupation: string;
  motherProfileImage: File | null;
  motherAadharImage: File | null;

  
  // Sibling Info
  siblingSameSchool: "yes" | "no" | "";
siblingIds: string[];


  // Address
  permanentAddress: Address;
  currentAddress: Address;
    schoolName: string;
  address: string;
}


const Inquiry = () => {
  const routes = all_routes;

  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [owner, setOwner] = useState<string[]>(['English','Spanish']);
  const [owner1, setOwner1] = useState<string[]>([]);
  const [owner2, setOwner2] = useState<string[]>([]);
  const [defaultDate, setDefaultDate] = useState<dayjs.Dayjs | null>(null);
  const [newContents, setNewContents] = useState<number[]>([0]);
  const location = useLocation();
   const [siblingSameSchool, setSiblingSameSchool] = useState<"yes" | "no">("no");
  const [siblingIds, setSiblingIds] = useState<string[]>([]);


const [inquiryFormData, setInquiryFormData] = useState<InquiryFormData>({
  academicYear: "",
  dateOfEnquiry: "",
  firstName: "",
  middleName: "",
  lastName: "",
  selectedClass: "",
  gender: "",
  dateOfBirth: "",
  primaryContact: "",
  email: "",
  suitableBatch: "",
  fatherName: "",
  fatherEmail: "",
  fatherPhone: "",
  fatherAadhar: "",
  fatherOccupation: "",
  fatherProfileImage: null,
  fatherAadharImage: null,

  motherName: "",
  motherPhone: "",
  motherEmail: "",
  motherAadhar: "",
  motherOccupation: "",
  motherProfileImage: null,
  motherAadharImage: null,
  siblingSameSchool: "",
   siblingIds: [] as string[],
  permanentAddress: {
    colony: "",
    area: "",
    landmark: "",
    city: "",
    state: "",
    pincode: "",
  },
  currentAddress: {
    colony: "",
    area: "",
    landmark: "",
    city: "",
    state: "",
    pincode: "",
  },
    schoolName:"",
  address:"",
});
const handleSiblingChange = (value: string, index: number) => {
  const updatedSiblings = [...inquiryFormData.siblingIds];
  updatedSiblings[index] = value;

  setInquiryFormData((prev) => ({
    ...prev,
    siblingIds: updatedSiblings,
  }));
};


const addSibling = () => {
  setInquiryFormData((prev) => ({
    ...prev,
    siblingIds: [...prev.siblingIds, ""],
  }));
};


const removeSibling = (index: number) => {
  const updated = [...inquiryFormData.siblingIds];
  updated.splice(index, 1);

  setInquiryFormData((prev) => ({
    ...prev,
    siblingIds: updated,
  }));
};



  const addNewContent = () => {
    setNewContents([...newContents, newContents.length]);
  };
  const removeContent = (index:any) => {
    setNewContents(newContents.filter((_, i) => i !== index));
  };
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
  
const handleSubmit = async () => {
  try {
    // ✅ Step 1: Convert to snake_case
    const snakePayload = preparePayload(inquiryFormData);

    // ✅ Step 2: Log and check
    console.log("📦 Payload to Send:", snakePayload);

    // ✅ Step 3: Send to API
    const res = await axiosInstance.post("/inquiry", snakePayload);

    console.log("✅ API Response:", res.data);
  } catch (err) {
    console.error("❌ API Error:", err);
  }
};
const payload = {
  academic_year: inquiryFormData.academicYear,
  date_of_enquiry: inquiryFormData.dateOfEnquiry,
  first_name: inquiryFormData.firstName,
  middle_name: inquiryFormData.middleName,
  last_name: inquiryFormData.lastName,
  selected_class: inquiryFormData.selectedClass,
  gender: inquiryFormData.gender,
  date_of_birth: inquiryFormData.dateOfBirth,
  primary_contact: inquiryFormData.primaryContact,
  email: inquiryFormData.email,
  suitable_batch: inquiryFormData.suitableBatch,

  father_name: inquiryFormData.fatherName,
  father_email: inquiryFormData.fatherEmail,
  father_phone: inquiryFormData.fatherPhone,
  father_aadhar: inquiryFormData.fatherAadhar,
  father_occupation: inquiryFormData.fatherOccupation,
  father_profile_image: inquiryFormData.fatherProfileImage,
  father_aadhar_image: inquiryFormData.fatherAadharImage,

  mother_name: inquiryFormData.motherName,
  mother_phone: inquiryFormData.motherPhone,
  mother_email: inquiryFormData.motherEmail,
  mother_aadhar: inquiryFormData.motherAadhar,
  mother_occupation: inquiryFormData.motherOccupation,
  mother_profile_image: inquiryFormData.motherProfileImage,
  mother_aadhar_image: inquiryFormData.motherAadharImage,

  sibling_same_school: inquiryFormData.siblingSameSchool,
  sibling_ids: inquiryFormData.siblingIds,

  permanent_address: {
    address: inquiryFormData.permanentAddress.colony,
    area: inquiryFormData.permanentAddress.area,
    landmark: inquiryFormData.permanentAddress.landmark,
    city: inquiryFormData.permanentAddress.city,
    state: inquiryFormData.permanentAddress.state,
    pincode: inquiryFormData.permanentAddress.pincode
  },
  current_address: {
    address: inquiryFormData.currentAddress.colony,
    area: inquiryFormData.currentAddress.area,
    landmark: inquiryFormData.currentAddress.landmark,
    city: inquiryFormData.currentAddress.city,
    state: inquiryFormData.currentAddress.state,
    pincode: inquiryFormData.currentAddress.pincode
  },

  school_name: inquiryFormData.schoolName,
  address: inquiryFormData.address
};

  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">
        <div className="content content-two">
          {/* Page Header */}
  
          {/* /Page Header */}
          <div className="row">
            <div className="col-md-12">
              <form>
             <div className="card">
  <div className="card-header bg-light">
    <div className="d-flex align-items-center">
      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
        <i className="ti ti-message-question fs-16" />
      </span>
      <h4 className="text-dark">Inquiry Form</h4>
    </div>
  </div>
     {/* PERSIIONAL INFORMATION */}
  <div className="card-body pb-1">
    <div className="row row-cols-xxl-5 row-cols-md-6">
      
      {/* Academic Year */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Academic Year</label>
        <CommonSelect
  className="select"
  options={academicYear}
  value={academicYear.find(opt => opt.value === inquiryFormData.academicYear) || undefined}
  onChange={(option) =>
    setInquiryFormData({ ...inquiryFormData, academicYear: option.value })
  }
/>
        </div>
      </div>

      {/* Date Of Enquiry */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Date Of Enquiry</label>
          <div className="input-icon position-relative">
            <DatePicker
              className="form-control datetimepicker"
              format={{ format: "DD-MM-YYYY", type: "mask" }}
              value={inquiryFormData.dateOfEnquiry ? dayjs(inquiryFormData.dateOfEnquiry) : null}
              onChange={(date) =>
                setInquiryFormData({
                  ...inquiryFormData,
                  dateOfEnquiry: date?.toISOString() || "",
                })
              }
              placeholder="Select Date"
            />
            <span className="input-icon-addon">
              <i className="ti ti-calendar" />
            </span>
          </div>
        </div>
      </div>

      {/* First Name */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">First Name</label>
          <input
            type="text"
            className="form-control"
            value={inquiryFormData.firstName}
            onChange={(e) =>
              setInquiryFormData({ ...inquiryFormData, firstName: e.target.value })
            }
          />
        </div>
      </div>

      {/* Middle Name */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Middle Name</label>
          <input
            type="text"
            className="form-control"
            value={inquiryFormData.middleName}
            onChange={(e) =>
              setInquiryFormData({ ...inquiryFormData, middleName: e.target.value })
            }
          />
        </div>
      </div>

      {/* Last Name */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Last Name</label>
          <input
            type="text"
            className="form-control"
            value={inquiryFormData.lastName}
            onChange={(e) =>
              setInquiryFormData({ ...inquiryFormData, lastName: e.target.value })
            }
          />
        </div>
      </div>

      {/* Class */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Class</label>
     <CommonSelect
  className="select"
  options={allClass}
  value={allClass.find(opt => opt.value === inquiryFormData.selectedClass) || undefined}
  onChange={(option) =>
    setInquiryFormData({ ...inquiryFormData, selectedClass: option.value })
  }
/>
        </div>
      </div>

      {/* Gender */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Gender</label>
      <CommonSelect
  className="select"
  options={gender}
  value={gender.find(opt => opt.value === inquiryFormData.gender) || undefined}
  onChange={(option) =>
    setInquiryFormData({ ...inquiryFormData, gender: option.value })
  }
/>
        </div>
      </div>

      {/* Date of Birth */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Date of Birth</label>
          <div className="input-icon position-relative">
            <DatePicker
              className="form-control datetimepicker"
              format={{ format: "DD-MM-YYYY", type: "mask" }}
              value={inquiryFormData.dateOfBirth ? dayjs(inquiryFormData.dateOfBirth) : null}
              onChange={(date) =>
                setInquiryFormData({
                  ...inquiryFormData,
                  dateOfBirth: date?.toISOString() || "",
                })
              }
              placeholder="Select Date"
            />
            <span className="input-icon-addon">
              <i className="ti ti-calendar" />
            </span>
          </div>
        </div>
      </div>

      {/* Primary Contact */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Primary Contact Number</label>
          <input
            type="text"
            className="form-control"
            value={inquiryFormData.primaryContact}
            onChange={(e) =>
              setInquiryFormData({ ...inquiryFormData, primaryContact: e.target.value })
            }
          />
        </div>
      </div>

      {/* Email Address */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Email Address</label>
          <input
            type="email"
            className="form-control"
            value={inquiryFormData.email}
            onChange={(e) =>
              setInquiryFormData({ ...inquiryFormData, email: e.target.value })
            }
          />
        </div>
      </div>

      {/* Suitable Batch */}
      <div className="col-xxl col-xl-3 col-md-6">
        <div className="mb-3">
          <label className="form-label">Suitable Batch</label>
          <CommonSelect
            className="select"
            options={suitableBatch}
          value={academicYear.find(opt => opt.value === inquiryFormData.academicYear) || undefined}

            onChange={(option) =>
              setInquiryFormData({ ...inquiryFormData, suitableBatch: option.value })
            }
          />
        </div>
      </div>
    </div>
  </div>
</div>

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
                <input
      type="text"
      className="form-control"
      placeholder="Enter Name"
      value={inquiryFormData.fatherName}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, fatherName: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Email</label>
              <input
      type="email"
      className="form-control"
      placeholder="Enter Email"
      value={inquiryFormData.fatherEmail}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, fatherEmail: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Phone</label>
            <input
      type="tel"
      className="form-control"
      placeholder="Enter Mobile No."
      value={inquiryFormData.fatherPhone}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, fatherPhone: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Aadhar</label>
    <input
      type="text"
      className="form-control"
      placeholder="Enter Aadhar No."
      value={inquiryFormData.fatherAadhar}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, fatherAadhar: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Occupation</label>
                 <input
      type="text"
      className="form-control"
      placeholder="Enter Occupation"
      value={inquiryFormData.fatherOccupation}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, fatherOccupation: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Father's Profile Image</label>
   <input
      type="file"
      className="form-control"
      accept="image/*"
      onChange={(e) =>
        setInquiryFormData({
          ...inquiryFormData,
          fatherProfileImage: e.target.files?.[0] || null,
        })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Father's Aadhar Image</label>
              <input type="file" className="form-control" accept="image/*,.pdf" />
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
       <input
      type="text"
      className="form-control"
      placeholder="Enter Name"
      value={inquiryFormData.motherName}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, motherName: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Phone</label>
              <input
      type="tel"
      className="form-control"
      placeholder="Enter Mobile No."
      value={inquiryFormData.motherPhone}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, motherPhone: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Email</label>
           <input
      type="email"
      className="form-control"
      placeholder="Enter Email"
      value={inquiryFormData.motherEmail}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, motherEmail: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Aadhar</label>
    <input
      type="text"
      className="form-control"
      placeholder="Enter Aadhar No."
      value={inquiryFormData.motherAadhar}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, motherAadhar: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Occupation</label>
         <input
      type="text"
      className="form-control"
      placeholder="Enter Occupation"
      value={inquiryFormData.motherOccupation}
      onChange={(e) =>
        setInquiryFormData({ ...inquiryFormData, motherOccupation: e.target.value })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Mother's Profile Image</label>
               <input
      type="file"
      className="form-control"
      accept="image/*"
      onChange={(e) =>
        setInquiryFormData({
          ...inquiryFormData,
          motherProfileImage: e.target.files?.[0] || null,
        })
      }
    />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Mother's Aadhar Image</label>
              <input type="file" className="form-control" accept="image/*,.pdf" />
            </div>
          </div>
        </div>
      </div>

      {/* SIBLING INFO */}
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
      <div className="col-xxl col-xl-3 col-md-6 mb-3">
        <label className="form-label">Is any sibling in the same school?</label>
        <div className="d-flex gap-3">
          <div className="form-check">
            <input
              className="form-check-input"
              type="radio"
              name="siblingSameSchool"
              id="siblingYes"
              value="yes"
              checked={siblingSameSchool === "yes"}
              onChange={() => setSiblingSameSchool("yes")}
            />
            <label className="form-check-label" htmlFor="siblingYes">Yes</label>
          </div>
          <div className="form-check">
            <input
              className="form-check-input"
              type="radio"
              name="siblingSameSchool"
              id="siblingNo"
              value="no"
              checked={siblingSameSchool === "no"}
              onChange={() => setSiblingSameSchool("no")}
            />
            <label className="form-check-label" htmlFor="siblingNo">No</label>
          </div>
        </div>
      </div>
    </div>

    {siblingSameSchool === "yes" && (
      <div className="col-12 mt-3">
        <label className="form-label">Sibling Student ID(s)</label>
{inquiryFormData.siblingIds.map((sibling, index) => (
  <div key={index} className="d-flex gap-2 mb-2">
    <input
      type="text"
      value={sibling}
      onChange={(e) => handleSiblingChange(e.target.value, index)}
      className="form-control"
    />
    <button
      type="button"
      onClick={() => removeSibling(index)}
      className="btn btn-danger btn-sm"
    >
      Remove
    </button>
  </div>
))}

<button
  type="button"
  onClick={addSibling}
  className="btn btn-primary btn-sm"
>
  Add Sibling
</button>

      </div>
    )}
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
              
              </form>


<button
  type="button"
  onClick={() => {

    console.log("Final Payload =>", payload);
    // Call your API with `payload`
  }}
  className="btn btn-primary"
>
  Submit & Log Payload
</button>



            </div>
          </div>
        </div>
      </div>

    </>
  );
};

export default Inquiry;

