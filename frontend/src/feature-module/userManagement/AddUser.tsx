import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
// import { feeGroup, feesTypes, paymentType } from '../../../core/common/selectoption/selectoption'
import { DatePicker } from "antd";
import dayjs from "dayjs";
import { all_routes } from "../router/all_routes";
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
} from "../../core/common/selectoption/selectoption";
import { TagsInput } from "react-tag-input-component";
import CommonSelect from "../../core/common/commonSelect";
import { useLocation } from "react-router-dom";
import { getClassesList } from "../../services/ClassData";

type ClassItem = {
  id: string;
  name: string;
};

const AddUser = () => {
  const routes = all_routes;
  const [classOptions, setClassOptions] = useState<{ label: string; value: string }[]>([]);

 const [allClass, setAllClass] = useState<{ label: string; value: string }[]>([]);
  const [loading, setLoading] = useState(false);
  const [isEdit, setIsEdit] = useState<boolean>(false);
  const [owner, setOwner] = useState<string[]>(['English','Spanish']);
  const [owner1, setOwner1] = useState<string[]>([]);
  const [owner2, setOwner2] = useState<string[]>([]);
  const [defaultDate, setDefaultDate] = useState<dayjs.Dayjs | null>(null);
  const [newContents, setNewContents] = useState<number[]>([0]);
  const location = useLocation();
   const [selectedBranches, setSelectedBranches] = useState<string[]>([]);

  const branches: string[] = ['Science', 'Commerce', 'Arts'];

  const handleCheckboxChange = (branch: string) => {
    setSelectedBranches((prev: string[]) =>
      prev.includes(branch)
        ? prev.filter((b) => b !== branch)
        : [...prev, branch]
    );
  };
  const addNewContent = () => {
    setNewContents([...newContents, newContents.length]);
  };
  const removeContent = (index:any) => {
    setNewContents(newContents.filter((_, i) => i !== index));
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
  
  return (
    <>
      {/* Page Wrapper */}
      <div className="page-wrapper">
        <div className="content content-two">
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
          {/* /Page Header */}
          <div className="row">
            <div className="col-md-12">
              <form>
                {/* Personal Information */}
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
                                />
                              </div>
                              <Link to="#" className="btn btn-primary mb-3">
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
                      {/* <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Academic Year</label>
                          <CommonSelect
                          className="select"
                          options={academicYear}
                          defaultValue={isEdit? academicYear[0]: undefined}
                        />
                        </div>
                      </div> */}
                      {/* <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Admission Number</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'AD9892434': undefined} />
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Admission Date</label>
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
                      </div> */}
                      {/* <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Roll Number</label>
                          <input type="text" className="form-control" defaultValue={isEdit? '35013': undefined} />
                        </div>
                      </div> */}
                
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">First Name</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'Ralph': undefined}/>
                        </div>
                      </div>
                   
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Last Name</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'claudia': undefined}/>
                        </div>
                      </div>
                    {/* <div className="col-xxl col-xl-3 col-md-6">
  <div className="mb-3">
    <label className="form-label">Class</label>
    <CommonSelect
      className="select"
      options={classOptions}
      defaultValue={isEdit ? classOptions[0] : undefined}
    />
  </div>
</div> */}

                      {/* <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Section</label>
                          <CommonSelect
                            className="select"
                            options={allSection}
                            defaultValue={isEdit?allSection[0]:undefined}
                          />
                        </div>
                      </div> */}
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
                      {/* <div className="col-xxl col-xl-3 col-md-6">
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
                      </div> */}
                      {/* <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Blood Group</label>
                          <CommonSelect
                            className="select"
                            options={bloodGroup}
                            defaultValue={isEdit? bloodGroup[0] : undefined}
                          />
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">House</label>
                          <CommonSelect
                            className="select"
                            options={house}
                            defaultValue={isEdit?house[0]:undefined}
                          />
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Religion</label>
                          <CommonSelect
                            className="select"
                            options={religion}
                            defaultValue={isEdit?religion[0]:undefined}
                          />
                        </div>
                      </div> */}
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Role</label>
                          <CommonSelect
                            className="select"
                            options={cast}
                            defaultValue={isEdit?cast[0]:undefined}
                          />
                        </div>
                      </div>
                      <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">
                             Contact Number
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
                      {/* <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Caste</label>
                          <input type="text" className="form-control" defaultValue={isEdit? 'Catholic': undefined}/>
                        </div>
                      </div> */}
                      {/* <div className="col-xxl col-xl-3 col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Suitable Batch</label>
                          <CommonSelect
                            className="select"
                            options={mothertongue}
                            defaultValue={isEdit?mothertongue[0]:undefined}
                          />
                        </div>
                      </div> */}
                      <div className="col-xxl col-xl-3 col-md-6">
      <div className="mb-3">
        <label className="form-label">Select Branch</label>
        <div className="d-flex flex-column">
          {branches.map((branch) => (
            <div className="form-check" key={branch}>
              <input
                type="checkbox"
                className="form-check-input"
                id={branch}
                value={branch}
                checked={selectedBranches.includes(branch)}
                onChange={() => handleCheckboxChange(branch)}
              />
              <label className="form-check-label " htmlFor={branch}>
                {branch}
              </label>
            </div>
          ))}
        </div>
      </div>
    </div>
                    </div>
                  </div>
                </div>
            
    
             
                <div className="text-end">
                  <button type="button" className="btn btn-light me-3">
                    Cancel
                  </button>
                  <Link to={routes.studentList} className="btn btn-primary">
                    Add User
                  </Link>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
     
    </>
  );
};

export default AddUser;
