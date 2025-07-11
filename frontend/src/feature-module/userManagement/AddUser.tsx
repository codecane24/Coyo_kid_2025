import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { useNavigate } from "react-router-dom";
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
import { getPermissionsList } from "../../services/Permissions";
import { createUser } from "../../services/UserData";
import { getRolelist } from "../../services/Roles";
type ClassItem = {
  id: string;
  name: string;
};
type Permission = {
  id: number;
  name: string;
  guard_name: string;
};



const AddUser = () => {
  const routes = all_routes;
const [permissionsList, setPermissionsList] = useState<Permission[]>([]);
const [roles, setRoles] = useState([]);
useEffect(() => {
  const fetchRoles = async () => {
    try {
      const response = await getRolelist(); // assuming this returns { data: [...] }
      if (response && response.data) {
        const formattedRoles = response.data.map((role: any) => ({
          label: role.name,  // adjust this based on API response
          value: role.id,    // adjust this based on API response
        }));
        setRoles(formattedRoles);
      }
    } catch (error) {
      console.error("Error fetching roles:", error);
    }
  };

  fetchRoles();
}, []);


const [selectedPermissions, setSelectedPermissions] = useState<string[]>([]);


  // Fetch permissions on mount
useEffect(() => {
 const fetchPermissions = async () => {
  try {
    const res = await getPermissionsList();
    console.log("Fetched permissions:", res);
    setPermissionsList(res); // ← if response is directly the array
  } 
  
  catch (err) {
    console.error("Error fetching permissions:", err);
    setPermissionsList([]); // fallback in error case
  }
};


  fetchPermissions();
  console.log(fetchPermissions)
}, []);
console.log(getPermissionsList)

    const handlePermissionChange = (e: React.ChangeEvent<HTMLInputElement>) => {
  const { value, checked } = e.target;
  setSelectedPermissions((prev) =>
    checked ? [...prev, value] : prev.filter((perm) => perm !== value)
  );
};

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
                      <h4 className="text-dark">Fill User Information</h4>
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
  <label className="form-label">Role</label>
  <CommonSelect
    className="select"
    options={roles}
    defaultValue={isEdit ? roles[0] : undefined}
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
                    </div>
<div className="card mt-4">
  {/* Section Header */}
  <div className="card-header bg-light">
    <div className="d-flex align-items-center">
      <span className="avatar avatar-sm bg-white text-dark me-2 d-flex justify-content-center align-items-center">
        <i className="ti ti-lock fs-18" />
      </span>
      <h5 className="mb-0 text-dark">User Permissions</h5>
    </div>
  </div>

  <div className="card-body">
    {permissionsList.map((parent: any) => (
      <div key={parent.id} className="mb-4">
        {/* Parent Permission Title */}
        <h6 className="text-dark fw-semibold mb-3 d-flex align-items-center">
          <i className="ti ti-shield-lock fs-16 me-2" />
          {parent.name.charAt(0).toUpperCase() + parent.name.slice(1).replace(/_/g, " ")}
        </h6>

        <div className="row g-3">
          {(parent.children || []).map((perm: any) => (
            <div key={perm.id} className="col-12 col-sm-6 col-md-4 col-lg-3">
              <div className="form-check">
   <input
  className="form-check-input"
  type="checkbox"
  id={`perm-${perm.id}`}
  name="permissions[]"
  value={perm.name}
  checked={selectedPermissions.includes(perm.name)}
  onChange={handlePermissionChange}
/>


   <label
  className="form-check-label text-dark"
  htmlFor={`perm-${perm.id}`}
>
  {perm.name}
</label>



              </div>
            </div>
          ))}
        </div>
      </div>
    ))}
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
