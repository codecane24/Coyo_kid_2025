import React, { useState , useEffect} from "react";
import { Link } from "react-router-dom";
import PredefinedDateRanges from "../../core/common/datePicker";
import { TableData } from "../../core/data/interface";
import Table from "../../core/common/dataTable/index";
import { permission } from "../../core/data/json/permission";
import { all_routes } from "../router/all_routes";
import TooltipOption from "../../core/common/tooltipOption";
import { getPermissionsList } from "../../services/Permissions";
import { createPermmisions } from "../../services/Permissions";
import axiosInstance from "../../utils/axiosInstance";
import { group } from "console";
import { getPermissionsGrpName } from "../../services/Permissions";
const DEFAULT_PERMISSIONS = ["Create", "Update", "Delete", "View"];

type PermissionItem = {
  id?: number;
  name: string;
};

type Permission = {
  id: number;
  name: string;
  children?: {
    id: number;
    name: string;
    children?: {
      id: number;
      name: string;
    }[];
  }[];
};


const Permission = () => {
  const [individualPermissionName, setIndividualPermissionName] = useState<string>("");
const [permissionName, setPermissionName] = useState("");
const [permissionModules, setPermissionModules] = useState<any[]>([]);
const [editingPermissionId, setEditingPermissionId] = useState<any[]>([]);


const [selectedGroup, setSelectedGroup] = useState<any>(null); // group to edit
const [allGroups, setAllGroups] = useState<any[]>([]); // from backend
// STATES
const [groupOptions, setGroupOptions] = useState<any[]>([]);
const [selectedGroupId, setSelectedGroupId] = useState("");
const [newPermissionInput, setNewPermissionInput] = useState("");
const [newPermissionList, setNewPermissionList] = useState<any[]>([]);
const [formMode, setFormMode] = useState<"add" | "edit">("add");

// FETCH GROUP NAMES ON MOUNT
useEffect(() => {
  const fetchGroupOptions = async () => {
    const res = await getPermissionsGrpName(); // Should return [{ id, name }]
    setGroupOptions(res);
  };
  fetchGroupOptions();
}, []);

// HANDLER - Add Permission Input to List
const handleAddPermissionToList = () => {
  if (!newPermissionInput.trim()) return;
  setNewPermissionList((prev) => [...prev, { name: newPermissionInput }]);
  setNewPermissionInput("");
};

// HANDLER - Add New Permissions to Group
const handleAddPermissionGroup = async () => {
  if (!selectedGroupId || !permissionName || newPermissionList.length === 0) {
    alert("Please fill all fields before saving.");
    return;
  }

 const payload = {
  group_id: Number(selectedGroupId),
  name: permissionName,
  permissions: newPermissionList.map(p => p.name),
};

console.log("Payload:", payload);

  try {
await axiosInstance.post("/permission", payload);

    alert("Permissions added successfully!");
    fetchPermissions(); // refresh permission list
    resetForm();
  } catch (error: any) {
    alert("Error adding permissions.");
    console.error("Add Permission Error:", error);
  }
};



// HANDLER - Update Existing Permissions in Group
const handleUpdatePermissionGroup = async () => {
  if (!selectedGroupId || !permissionName || newPermissionList.length === 0) {
    alert("Please fill all fields before updating.");
    return;
  }

  // You must find the selected permission module's ID
  const selectedModule = permissionModules.find((mod) => mod.name === permissionName);
  const permissionId = selectedModule?.id;

  if (!permissionId) {
    alert("Permission ID not found for the selected name.");
    return;
  }

const payload = {
  
   group_id: Number(selectedGroupId),
  name: permissionName,
  permissions: newPermissionList.map(p => p.name),
};
console.log("Payload:", JSON.stringify(payload, null, 2));

  try {
    await axiosInstance.put(`/permission/${editingPermissionId}`, payload);

    alert("Permissions updated successfully!");
    fetchPermissions();
    resetForm();
  } catch (error: any) {
    alert("Error updating permissions.");
    console.error("Update Permission Error:", error);
  }
};

// Reset Form
const resetForm = () => {
  setSelectedGroupId("");
  setNewPermissionInput("");
  setNewPermissionList([]);
  setFormMode("add");
  document.getElementById("add_role_close_btn")?.click();
};

// HANDLER - When Clicking Edit Button
const handleEditClick = (group: any, module: any) => {
  setFormMode("edit");
  setSelectedGroupId(group.id);
  setPermissionName(module.name);
  setEditingPermissionId(module.id); // << store module id directly
  setPermissionModules(group.modules);
  setNewPermissionList(
    module.children.map((p: any) => ({
      id: p.id,
      name: p.name,
    }))
  );
};






   const fetchPermissions = async () => {
  const res = await axiosInstance.get("/permission");

  setPermissions(res.data);
  console.log("it is a permissions data" +  res)
};

    const [permissions, setPermissions] = useState<Permission[]>([]);

const [editGroupId, setEditGroupId] = useState<number | null>(null); // ID for editing

  const routes = all_routes
  const [permissionGroups, setPermissionGroups] = useState<
    { name: string; permissions: string[] }[]
  >([
    {
      name: "",
      permissions: ["", "", "", ""],
    },
  ]);

  const [newGroupName, setNewGroupName] = useState<string>("");

  const [showForm, setShowForm] = useState<boolean>(false);

  const [editingGroupIndex, setEditingGroupIndex] = useState<number | null>(null);
  const [editGroupName, setEditGroupName] = useState<string>("");
  const [editPermissions, setEditPermissions] = useState<string[]>([]);
  const [editNewPermission, setEditNewPermission] = useState<string>("");

 

  const handleDeleteGroup = (index: number) => {
    const updated = [...permissionGroups];
    updated.splice(index, 1);
    setPermissionGroups(updated);
  };

  const startEditingGroup = (index: number) => {
    setEditingGroupIndex(index);
    setEditGroupName(permissionGroups[index].name);
    setEditPermissions([...permissionGroups[index].permissions]);
    setEditNewPermission("");
  };

  const handleSaveEditedGroup = () => {
    if (editingGroupIndex === null) return;

    const updated = [...permissionGroups];
    updated[editingGroupIndex] = {
      name: editGroupName.trim(),
      permissions: editPermissions,
    };

    setPermissionGroups(updated);
    setEditingGroupIndex(null);
    setEditGroupName("");
    setEditPermissions([]);
  };

  const handleAddEditPermission = () => {
    const trimmed = editNewPermission.trim();
    if (trimmed && !editPermissions.includes(trimmed)) {
      setEditPermissions([...editPermissions, trimmed]);
      setEditNewPermission("");
    }
  };

  const handleRemoveEditPermission = (perm: string) => {
    setEditPermissions(editPermissions.filter((p) => p !== perm));
  }







useEffect(() => {
  const fetchPermissions = async () => {
    try {
      const data = await getPermissionsList();
      setPermissions(data); // ✅ No transformation needed
      console.log("Permissions Data:", data);
    } catch (error) {
      console.error("Error fetching permissions", error);
    }
  };

  fetchPermissions();
}, []);


  return (
    <div>
      <>
        {/* Page Wrapper */}
        <div className="page-wrapper">
          <div className="content">
            {/* Page Header */}
            <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
              <div className="my-auto mb-2">
                <h3 className="page-title mb-1"> Permissions Master</h3>
                <nav>
                  <ol className="breadcrumb mb-0">
                    <li className="breadcrumb-item">
                      <Link to={routes.adminDashboard}>Dashboard</Link>
                    </li>
                    <li className="breadcrumb-item">
                      <Link to="#">User Management</Link>
                    </li>
                    <li className="breadcrumb-item active" aria-current="page">
                     Permissions Master
                    </li>
                  </ol>
                </nav>
              </div>
              <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
            
                <div className="mb-2">
                  <Link
                    to="#"
                    className="btn btn-primary d-flex align-items-center"
                   data-bs-toggle="modal"
  data-bs-target="#add_role"
  onClick={() => {
    setFormMode("add");
    setNewGroupName("");
    setNewPermissionInput("");
    setNewPermissionList([]);
  }}
                  >
                    <i className="ti ti-square-rounded-plus me-2" />
                    Add New Permission
                  </Link>
                </div>
              </div>
            </div>
           
            <div className="card">
<div className="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
  {/* Title */}
  <h4 className="mb-0">Permissions List</h4>

  {/* Search + Sort */}
  <div className="d-flex align-items-center gap-2 flex-wrap">
    {/* Search Bar - smaller width */}
    <div className="input-group" style={{ width: "180px" }}>
      <span className="input-group-text bg-white px-2">
        <i className="ti ti-search" />
      </span>
      <input
        type="text"
        className="form-control form-control-sm"
        placeholder="Search"
      />
    </div>

    {/* Sort Dropdown */}
    <div className="dropdown">
      <Link
        to="#"
        className="btn btn-outline-light bg-white btn-sm dropdown-toggle"
        data-bs-toggle="dropdown"
      >
        <i className="ti ti-sort-ascending-2 me-2" />
        Sort
      </Link>
      <ul className="dropdown-menu p-2">
        <li>
          <Link to="#" className="dropdown-item rounded-1 active">
            Ascending
          </Link>
        </li>
        <li>
          <Link to="#" className="dropdown-item rounded-1">
            Descending
          </Link>
        </li>
        <li>
          <Link to="#" className="dropdown-item rounded-1">
            Recently Viewed
          </Link>
        </li>
        <li>
          <Link to="#" className="dropdown-item rounded-1">
            Recently Added
          </Link>
        </li>
      </ul>
    </div>
  </div>
</div>


         <div className="card-body p-0 py-3">
  {/* Permissions List */}
<div className="card-body p-4">
      
{permissions.map((group: any) => (
  <div key={group.id} className="mb-5 pb-4 border-bottom">
    <h4 className="fw-bold text-capitalize">{group.name}</h4>

    {/* Modules (Permission Names) */}
    {group.modules.map((module: any) => (
      <div key={module.id} className="mb-4 ps-3 border-start border-3 border-primary">
        <div className="d-flex justify-content-between align-items-center mb-2">
          <h6 className="fw-semibold text-primary text-capitalize mb-0">
            {module.name.replace(/_/g, ' ')}
          </h6>

          <button
            type="button"
            className="btn btn-sm btn-outline-primary"
            data-bs-toggle="modal"
            data-bs-target="#add_role"
            onClick={() => handleEditClick(group, module)}
          >
            ✏️ Edit
          </button>
        </div>

        <div className="row">
          {module.children?.map((perm: any) => (
            <div className="col-md-3 col-sm-6 mb-2" key={perm.id}>
              <div className="form-check">
                <input
                  type="checkbox"
                  className="form-check-input"
                  id={`perm-${perm.id}`}
                />
                <label className="form-check-label text-capitalize" htmlFor={`perm-${perm.id}`}>
                  {perm.name.replace(/_/g, ' ')}
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

            </div>
       
          </div>
        </div>
   {/* Add/Edit Permission Modal */}
<div className="modal fade" id="add_role">
  <div className="modal-dialog modal-dialog-centered">
    <div className="modal-content">
      <div className="modal-header">
        <h4 className="modal-title">
          {formMode === "add" ? "Add New Permissions" : "Edit Permission Group"}
        </h4>
        <button
          type="button"
          className="btn-close custom-btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"
          id="add_role_close_btn"
        >
          <i className="ti ti-x" />
        </button>
      </div>

      <div className="mt-4 border rounded p-3 bg-light">
        {/* Dropdown: Group Name */}
        <div className="mb-3">
          <label className="form-label">Permission Group</label>
          <select
            className="form-select"
            value={selectedGroupId}
            onChange={(e) => setSelectedGroupId(e.target.value)}
            disabled={formMode === "edit"}
          >
            <option value="">Select Group</option>
            {groupOptions.map((grp) => (
              <option key={grp.id} value={grp.id}>
                {grp.name}
              </option>
            ))}
          </select>
        </div>

        {/* Input: Permission Name */}
        <div className="mb-3">
          <label className="form-label">Permission Name</label>
          <input
            type="text"
            className="form-control"
            placeholder="e.g. class, role, user"
            value={permissionName}
            onChange={(e) => setPermissionName(e.target.value)}
          />
        </div>

        {/* Add Permission */}
        <div className="mb-3">
          <label className="form-label">Add Permissions</label>
          <div className="input-group mb-2">
            <input
              type="text"
              className="form-control"
              placeholder="e.g. Approve Product"
              value={newPermissionInput}
              onChange={(e) => setNewPermissionInput(e.target.value)}
            />
            <button
              className="btn btn-outline-primary"
              type="button"
              onClick={handleAddPermissionToList}
            >
              + Add to List
            </button>
          </div>

          {newPermissionList.length > 0 && (
            <ul className="list-group">
              {newPermissionList.map((perm, idx) => (
                <li
                  key={perm.id ?? idx}
                  className="list-group-item py-1 d-flex gap-2 align-items-center"
                >
                  <input
                    type="text"
                    className="form-control"
                    value={perm.name}
                    onChange={(e) => {
                      const updated = [...newPermissionList];
                      updated[idx].name = e.target.value;
                      setNewPermissionList(updated);
                    }}
                  />
                  <button
                    className="btn btn-sm btn-outline-danger"
                    type="button"
                    onClick={() => {
                      const filtered = newPermissionList.filter((_, i) => i !== idx);
                      setNewPermissionList(filtered);
                    }}
                  >
                    ❌
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>

        {/* Action Buttons */}
        <div className="d-flex gap-2">
          <button
            className="btn btn-primary"
            onClick={() =>
              formMode === "add" ? handleAddPermissionGroup() : handleUpdatePermissionGroup()
            }
          >
            Save
          </button>

          <button
            className="btn btn-secondary"
            onClick={resetForm}
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</div>


     
      </>
    </div>
  );
};

export default Permission;
