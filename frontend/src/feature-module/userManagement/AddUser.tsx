import React, { useEffect, useState } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { DatePicker } from "antd";
import dayjs from "dayjs";
import CommonSelect from "../../core/common/commonSelect";
import BranchMultiSelect from "../common/BranchMultiSelect";
import { department, gender, status } from "../../core/common/selectoption/selectoption";
import { getPermissionsList } from "../../services/Permissions";
import { getRolelist } from "../../services/Roles";
import { createUser } from "../../services/UserData";

import axiosInstance from "../../utils/axiosInstance";
import { Eye, EyeOff } from "lucide-react"; 
import { updateUser } from "../../services/UserData";
import { getUserById } from "../../services/UserData";
import { useParams } from "react-router-dom";
import { all_routes } from "../router/all_routes";
type Permission = {
  id: number;
  name: string;
  modules?: {
    id: number;
    name: string;
    children?: { id: number; name: string }[];
  }[];
};

const AddUser = () => {
  const navigate = useNavigate();
  const routes = all_routes;
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [email, setEmail] = useState("");
  const [mobile, setContact] = useState("");
  const [genderValue, setGenderValue] = useState<any>(null);
  const [statusValue, setStatusValue] = useState<any>(null);
  const [roleName, setRoleName] = useState<any>(null);
  const [imageFile, setImageFile] = useState<File | null>(null);
  const [permissionsList, setPermissionsList] = useState<Permission[]>([]);
const [selectedPermissions, setSelectedPermissions] = useState<number[]>([]);

  const [selectedBranches, setSelectedBranches] = useState<number[]>([]);
  const [roles, setRoles] = useState<any[]>([]);
const[departmentValue, setdepartmentValue] =  useState<any>(null);
const [password, setPassword] = useState("");
const [confirmPassword, setConfirmPassword] = useState("");
const [showPassword, setShowPassword] = useState(false);
const [showConfirmPassword, setShowConfirmPassword] = useState(false);
const [imagePreviewUrl, setImagePreviewUrl] = useState<string | null>(null);

const location = useLocation();
const { id } = useParams();
const userId = id; // ✅ convert to number
const isEdit = !!userId;
console.log("Route ID:", id, "Parsed UserID:", userId, "Is Edit:", isEdit);
useEffect(() => {
  const fetchUserDetails = async () => {
    if (isEdit && userId) {
      try {
        const res = await getUserById(userId);
        const user = res.data.data;

        console.log("✅ it is user data for edit:", user);

        // ✅ Set basic fields
        setFirstName(user.first_name || "");
        setLastName(user.last_name || "");
        setEmail(user.email || "");
        setContact(user.mobile || "");

        // ✅ Safely set dropdown fields only if values exist
        setGenderValue(user.gender ? { label: user.gender, value: user.gender } : null);
        setStatusValue(user.status ? { label: user.status, value: user.status } : null);
        setdepartmentValue(user.department_id && user.department_id !== "0" ? { label: user.department_id, value: user.department_id } : null);
        setRoleName(user.type ? { label: user.type , value: user.type } : null);

        // ✅ Set arrays
setSelectedBranches((user.branches || []).map((b: string) => Number(b)));
setSelectedPermissions((user.permissions || []).map(Number));


           // ✅ Set image URL
        if (user.profile_image) {
          setImagePreviewUrl(user.profile_image); // 👈 You need this state
        }
        // ✅ Don’t prefill passwords
        setPassword("");
        setConfirmPassword("");
      } catch (err) {
        console.error("❌ Failed to fetch user", err);
      }
    }
  };

  fetchUserDetails();
}, [isEdit, userId]);




  useEffect(() => {
    const fetchRoles = async () => {
      const res = await getRolelist();
      const formatted = res.data.map((r: any) => ({
        label: r.name,
        value: r.name,
      }));
      setRoles(formatted);
    };

    const fetchPermissions = async () => {
      const res = await getPermissionsList();
      setPermissionsList(res);
    };

    fetchRoles();
    fetchPermissions();
  }, []);

  // const handlePermissionChange = (e: React.ChangeEvent<HTMLInputElement>) => {
  //   const { value, checked } = e.target;
  //   setSelectedPermissions((prev) =>
  //     checked ? [...prev, value] : prev.filter((p) => p !== value)
  //   );
  // };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file && file.size > 4194304) {
      alert("Image must be less than 4MB");
      e.target.value = "";
      setImageFile(null);
      return;
    }
    setImageFile(file || null);
  };
const validateForm = () => {
  if (!firstName || !email || !mobile) {
    alert("Please fill all required fields.");
    return false;
  }

  if (!isEdit) {
    if (!lastName || !genderValue || !roleName) {
      alert("Please fill all dropdowns and last name.");
      return false;
    }

    if (!imageFile) {
      alert("Please upload an image.");
      return false;
    }

    if (password.length < 6) {
      alert("Password must be at least 6 characters.");
      return false;
    }

    if (password !== confirmPassword) {
      alert("Passwords do not match.");
      return false;
    }

    if (selectedPermissions.length < 1) {
      alert("Please select at least one permission.");
      return false;
    }
  } else {
    // ✅ For EDIT mode — only validate password if user typed something
    if (password || confirmPassword) {
      if (password.length < 6) {
        alert("Password must be at least 6 characters.");
        return false;
      }
      if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return false;
      }
    }
  }

  return true;
};




const resetForm = () => {
  setFirstName("");
  setLastName("");
  setEmail("");
  setContact("");
  setGenderValue(null);
  setStatusValue(null);
  setRoleName(null);
  setdepartmentValue(null);
  setPassword("");
  setConfirmPassword("");
  setShowPassword(false);
  setShowConfirmPassword(false);
  setImageFile(null);
  setSelectedBranches([]);
  setSelectedPermissions([]);
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  // if (password.length < 6) {
  //   alert("Password must be at least 6 characters.");
  //   return;
  // }

  // if (password !== confirmPassword) {
  //   alert("Passwords do not match.");
  //   return;
  // }

  try {
    if (isEdit && userId) {
      // ✅ EDIT: Use JSON format
   const updatePayload = new FormData();
updatePayload.append("first_name", firstName);
updatePayload.append("last_name", lastName);
updatePayload.append("email", email);
updatePayload.append("mobile", mobile);
updatePayload.append("gender", genderValue?.value);
updatePayload.append("department", departmentValue?.value);
updatePayload.append("status", statusValue?.value);
updatePayload.append("type", roleName?.value); // or whatever type
// ✅ Append branches one by one
selectedBranches.forEach((branchId) => {
  updatePayload.append("branches[]", String(branchId));
});

// ✅ Append permissions one by one
selectedPermissions.forEach((permId) => {
  updatePayload.append("permissions[]", String(permId));
});
updatePayload.append("password", password);

if (imageFile) {
  updatePayload.append("profile_image", imageFile);
}

 // Debug payload
      updatePayload.forEach((value, key) => {
        console.log(`${key}:`, value);
      });
      const res = await updateUser(userId, updatePayload);
      alert("User updated successfully");
      console.log(res);
      
    } else {
      // ✅ ADD: Use FormData for file upload
      const payload = new FormData();
      payload.append("first_name", firstName);
      payload.append("last_name", lastName);
      payload.append("email", email);
      payload.append("mobile", mobile);
      payload.append("gender", genderValue?.value);
      payload.append("department", departmentValue?.value);
      payload.append("status", statusValue?.value);
      payload.append("type", roleName?.value);

   selectedBranches.forEach((branchId) => {
  payload.append("branches[]", branchId.toString());
});

selectedPermissions.forEach((permId) => {
  payload.append("permissions[]", permId.toString());
});
      payload.append("password", password);

      if (imageFile) payload.append("profile_image", imageFile);

      // Debug payload
      payload.forEach((value, key) => {
        console.log(`${key}:`, value);
      });

      const res = await createUser(payload);
      alert("User created successfully");
      console.log(res);
    }

    resetForm();
    navigate(all_routes.manageusers);

  } catch (error) {
    console.error("Error submitting form", error);
    alert("Failed to create or update user.");
  }
};

const handleCancel = () => {
  if (!isEdit) {
    resetForm(); // Clear only if it's ADD mode
  }
  navigate(routes.manageusers);  // Navigate in both cases
};
  return (
    <div className="page-wrapper">
      <div className="content content-two">
        <h3 className="mb-4">Add User</h3>
        <div className="row">
          <div className="col-md-12">
            <form id="addUserForm">
              <div className="row row-cols-xxl-5 row-cols-md-6">
                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">First Name</label>
                    <input
                      name="firstName"
                      className="form-control"
                      value={firstName}
                      onChange={(e) => setFirstName(e.target.value)}
                    />
                  </div>
                </div>

                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Last Name</label>
                    <input
                      name="lastName"
                      className="form-control"
                      value={lastName}
                      onChange={(e) => setLastName(e.target.value)}
                    />
                  </div>
                </div>

                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Email</label>
                    <input
                      name="email"
                      className="form-control"
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                    />
                  </div>
                </div>

                <div className="col-xxl col-xl-3 col-md-6">
                  <div className="mb-3">
                    <label className="form-label">Mobile Number</label>
                    <input
                      name="mobile"
                      className="form-control"
                      value={mobile}
                      onChange={(e) => setContact(e.target.value)}
                    />
                  </div>
                </div>

                <div className="col-xxl col-xl-3 col-md-6">
                  <label className="form-label">Gender</label>
                  <CommonSelect
                    options={gender}
                    value={genderValue}
                    onChange={setGenderValue}
                    className="select"
                    
                  />
                </div>
                    <div className="col-xxl col-xl-3 col-md-6">
                  <label className="form-label">Department</label>
                  <CommonSelect
                    options={department}
                    value={departmentValue}
                    onChange={setdepartmentValue}
                    className="select"
                    
                  />
                </div>

 <div className="col-xxl col-xl-3 col-md-6">
                  <label className="form-label">Status</label>
                  <CommonSelect
                    options={status}
                    value={statusValue}
                    onChange={setStatusValue}
                    className="select"
                    
                  />
                </div>
                <div className="col-xxl col-xl-3 col-md-6">
                  <label className="form-label">Role</label>
                  <CommonSelect
                    options={roles}
                    value={roleName}
                    onChange={setRoleName}
                    className="select"
                  />
                </div>

                <BranchMultiSelect
                  selectedBranchIds={selectedBranches}
                  onChange={setSelectedBranches}
                />

             <div className="col-xxl col-xl-3 col-md-6">
  <label className="form-label">Profile Image</label>
  {imagePreviewUrl && (
    <div style={{ marginBottom: "10px" }}>
      <img
        src={imagePreviewUrl}
        alt="Profile Preview"
        width={120}
        style={{ borderRadius: "8px" }}
      />
    </div>
  )}
  <input
    type="file"
    accept="image/*"
    className="form-control"
    onChange={(e) => {
      const file = e.target.files?.[0];
      if (file && file.size > 4194304) {
        alert("Image must be less than 4MB");
        return;
      }
      setImageFile(file || null);
      if (file) {
        const reader = new FileReader();
        reader.onloadend = () => {
          setImagePreviewUrl(reader.result as string);
        };
        reader.readAsDataURL(file);
      }
    }}
  />
</div>

 {/* Password */}
<div className="col-xxl col-xl-3 col-md-6">
  <div className="mb-3 position-relative">
    <label className="form-label">Password</label>
    <input
      type={showPassword ? "text" : "password"}
      className="form-control pe-5"
      value={password}
      onChange={(e) => setPassword(e.target.value)}
      placeholder="Enter password"
    />
    <span
      className="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer"
      onClick={() => setShowPassword(!showPassword)}
    >
      {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
    </span>
  </div>
</div>

{/* Confirm Password */}
<div className="col-xxl col-xl-3 col-md-6">
  <div className="mb-3 position-relative">
    <label className="form-label">Confirm Password</label>
    <input
      type={showConfirmPassword ? "text" : "password"}
      className="form-control pe-5"
      value={confirmPassword}
      onChange={(e) => setConfirmPassword(e.target.value)}
      placeholder="Confirm password"
    />
    <span
      className="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer"
      onClick={() => setShowConfirmPassword(!showConfirmPassword)}
    >
      {showConfirmPassword ? <EyeOff size={18} /> : <Eye size={18} />}
    </span>
  </div>
</div>


              </div>

              {/* Permissions */}
              <div className="card mt-4">
                <div className="card-header bg-light">
                  <h5>User Permissions</h5>
                </div>
                <div className="card-body">
                  {permissionsList.map((parent) => (
                    <div key={parent.id} className="mb-3">
                      <h6>{parent.name.replace(/_/g, " ")}</h6>
                      {parent.modules?.map((mod) => (
                        <div key={mod.id}>
                          <p className="fw-bold">{mod.name}</p>
                          <div className="row g-2">
                            {mod.children?.map((perm) => (
                              <div key={perm.id} className="col-6 col-lg-4 col-xl-3">
                                <div className="form-check">
                                 <input
  className="form-check-input"
  type="checkbox"
  id={`perm-${perm.id}`}
  name="permissions[]"
  value={perm.id} // ✅ use ID instead of name
  checked={selectedPermissions.includes(perm.id)}
  onChange={(e) => {
    const id = Number(e.target.value);
    const checked = e.target.checked;
    setSelectedPermissions((prev) =>
      checked ? [...prev, id] : prev.filter((p) => p !== id)
    );
  }}
/>
<label className="form-check-label" htmlFor={`perm-${perm.id}`}>
  {perm.name}
</label>

                                </div>
                              </div>
                            ))}
                          </div>
                        </div>
                      ))}
                    </div>
                  ))}
                </div>
              </div>

              <div className="text-end mt-3">
      <button
  type="button"
  className="btn btn-light me-3"
  onClick={handleCancel}
>
  Cancel
</button>


                <button type="button" className="btn btn-primary" onClick={handleSubmit}>
                  Add User
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AddUser;
