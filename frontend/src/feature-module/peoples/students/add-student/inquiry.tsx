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

  const handleSiblingChange = (value: string, index: number) => {
    const updated = [...siblingIds];
    updated[index] = value;
    setSiblingIds(updated);
  };

  const addSibling = () => {
    setSiblingIds([...siblingIds, ""]);
  };

  const removeSibling = (index: number) => {
    const updated = [...siblingIds];
    updated.splice(index, 1);
    setSiblingIds(updated);
  };
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
  siblingIds: [],

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
                {/* Personal Information */}
                <div className="card">
                  <div className="card-header bg-light">
                    <div className="d-flex align-items-center">
                      <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
              <i className="ti ti-message-question fs-16" />

                      </span>
                      <h4 className="text-dark">Inquiry Form</h4>
                    </div>
                  </div>
                  <div className="card-body pb-1">
                    <div className="row">
                      <div className="col-md-12">
                       
                      </div>
                    </div>
                    <div className="row row-cols-xxl-5 row-cols-md-6">
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Academic Year</label>
                          <CommonSelect
                          className="select"
                          options={academicYear}
                          defaultValue={isEdit? academicYear[0]: undefined}
                        />
                        </div>
                      </div>
                    
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Date Of Enquiry</label>
                          <div className="input-icon position-relative">
                          {isEdit? <DatePicker
                                className="form-control datetimepicker"
                                format={{
                                  format: "DD-MM-YYYY",
                                  type: "mask",
                                }}
                                value={defaultDate}
                                placeholder="Select Date"
                              /> : <DatePicker
                              className="form-control datetimepicker"
                              format={{
                                format: "DD-MM-YYYY",
                                type: "mask",
                              }}
                              defaultValue=""
                              placeholder="Select Date"
                            />}
                            <span className="input-icon-addon">
                              <i className="ti ti-calendar" />
                            </span>
                          </div>
                        </div>
                      </div>
                     
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">First Name</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'Ralph': undefined}/>
                        </div>
                      </div>
                         <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Middle Name</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'claudia': undefined}/>
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Last Name</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'claudia': undefined}/>
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Class</label>
                          <CommonSelect
                            className="select"
                            options={allClass}
                            defaultValue={isEdit?allClass[0]:undefined}
                          />
                        </div>
                      </div>
                
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Gender</label>
                          <CommonSelect
                            className="select"
                            options={gender}
                            defaultValue={isEdit?gender[0]:undefined}
                          />
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Date of Birth</label>
                          <div className="input-icon position-relative">
                          {isEdit? <DatePicker
                                className="form-control datetimepicker"
                                format={{
                                  format: "DD-MM-YYYY",
                                  type: "mask",
                                }}
                                value={defaultDate}
                                placeholder="Select Date"
                              /> : <DatePicker
                              className="form-control datetimepicker"
                              format={{
                                format: "DD-MM-YYYY",
                                type: "mask",
                              }}
                              defaultValue=""
                              placeholder="Select Date"
                            />}
                            <span className="input-icon-addon">
                              <i className="ti ti-calendar" />
                            </span>
                          </div>
                        </div>
                      </div>
             
                
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                            Primary Contact Number
                          </label>
                          <input type="text" className="form-control" defaultValue={isEdit? '+1 46548 84498': undefined}/>
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Email Address</label>
                          <input type="email" className="form-control" defaultValue={isEdit? 'jan@example.com': undefined}/>
                        </div>
                      </div>
                           <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Suitable Batch</label>
                          <CommonSelect
                            className="select"
                            options={suitableBatch}
                            defaultValue={isEdit?suitableBatch[0]:undefined}
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
              <input type="text" className="form-control" placeholder="Enter Name" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Email</label>
              <input type="email" className="form-control" placeholder="Enter Email" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Phone</label>
              <input type="text" className="form-control" placeholder="Enter Phone" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Aadhar</label>
              <input type="text" className="form-control" placeholder="Enter Aadhar" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Occupation</label>
              <input type="text" className="form-control" placeholder="Enter Occupation" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Father's Profile Image</label>
              <input type="file" className="form-control" accept="image/*" />
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
              <input type="text" className="form-control" placeholder="Enter Name" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Phone</label>
              <input type="text" className="form-control" placeholder="Enter Phone" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Email</label>
              <input type="email" className="form-control" placeholder="Enter Email" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Aadhar</label>
              <input type="text" className="form-control" placeholder="Enter Aadhar" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Occupation</label>
              <input type="text" className="form-control" placeholder="Enter Occupation" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Mother's Profile Image</label>
              <input type="file" className="form-control" accept="image/*" />
            </div>
            <div className="col-xxl col-xl-3 col-md-6 mb-3">
              <label className="form-label">Mother's Aadhar Image</label>
              <input type="file" className="form-control" accept="image/*,.pdf" />
            </div>
          </div>
        </div>
      </div>

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
              {siblingIds.map((id, index) => (
                <div key={index} className="d-flex align-items-center gap-2 mb-2">
                  <select
                    value={id}
                    onChange={(e) => handleSiblingChange(e.target.value, index)}
                    className="form-select w-auto"
                  >
                    <option value="">Select ID</option>
                    <option value="STD2123">STD2123</option>
                    <option value="STD4566">STD4566</option>
                    <option value="STD7890">STD7890</option>
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
            </div>
          </div>
        </div>
      </div>

    </>
  );
};

export default Inquiry;

