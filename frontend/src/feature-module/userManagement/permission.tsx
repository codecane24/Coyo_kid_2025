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

const DEFAULT_PERMISSIONS = ["Create", "Update", "Delete", "View"];
type Permission = {
  id: number;
  name: string;
  children?: {
    id: number;
    name: string;
  }[];
};

type PermissionItem = { id?: number; name: string };
const Permission = () => {
  
   const fetchPermissions = async () => {
  const res = await axiosInstance.get("/permission");
  setPermissions(res.data);
};

    const [permissions, setPermissions] = useState<Permission[]>([]);
const [formMode, setFormMode] = useState<"add" | "edit">("add");
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
const [newPermissionList, setNewPermissionList] = useState<PermissionItem[]>([]);
  const [newGroupName, setNewGroupName] = useState<string>("");
  const [newPermissionInput, setNewPermissionInput] = useState<string>("");
  const [showForm, setShowForm] = useState<boolean>(false);

  const [editingGroupIndex, setEditingGroupIndex] = useState<number | null>(null);
  const [editGroupName, setEditGroupName] = useState<string>("");
  const [editPermissions, setEditPermissions] = useState<string[]>([]);
  const [editNewPermission, setEditNewPermission] = useState<string>("");

  // Add new custom permission
const handleAddPermissionToList = () => {
  if (newPermissionInput.trim()) {
    setNewPermissionList([
      ...newPermissionList,
      { name: newPermissionInput.trim() }
    ]);
    setNewPermissionInput("");
  }
};



  // const handleAddPermissionGroup = () => {
  //   const trimmedGroupName = newGroupName.trim();
  //   if (!trimmedGroupName) return;

  //   const finalPermissions = Array.from(
  //     new Set([...DEFAULT_PERMISSIONS, ...newPermissionList])
  //   );

  //   setPermissionGroups([
  //     ...permissionGroups,
  //     { name: trimmedGroupName, permissions: finalPermissions },
  //   ]);

  //   setNewGroupName("");
  //   setNewPermissionList([]);
  //   setNewPermissionInput("");
  //   setShowForm(false);
  // };

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
const handleAddPermissionGroup = async () => {
  if (!newGroupName.trim()) return alert("Group name is required");
  if (newPermissionList.length === 0) return alert("Please add permissions");

  // ✅ Define the payload and log it
  const payload = {
    name: newGroupName.trim(),
    permissions: newPermissionList, // array of { name: string, id?: number }
  };

  console.log("Creating permission group with payload:", JSON.stringify(payload, null, 2));
console.log(payload)
  try {
    await createPermmisions(payload);
    alert("Permission group added");
    document.getElementById("add_role_close_btn")?.click(); // close modal
    // fetchPermissions(); // reload data
  } catch (err: any) {
    console.error("Failed to create group:", err.response?.data || err.message);
    alert("Failed to add group");
  }
};


const handleUpdatePermissionGroup = async () => {
  if (!editGroupId) return;
  if (!newGroupName.trim()) return alert("Group name is required");
  if (newPermissionList.length === 0) return alert("Please add permissions");

  const payload = {
    name: newGroupName,
    permissions: newPermissionList, // mix of old (with id) + new (without id)
  };
console.log(payload);

  console.log("PUT to /permission/" + editGroupId);
  console.log("Payload:", JSON.stringify(payload, null, 2));

  try {
    await axiosInstance.put(`/permission/${editGroupId}/`, payload);
    alert("Permission group updated");
    document.getElementById("add_role_close_btn")?.click();
    fetchPermissions();
  } catch (err: any) {
    console.error("Update failed:", err.response?.data || err.message);
    alert("Failed to update group");
  }
};





  useEffect(() => {
    const fetchPermissions = async () => {
      try {
        const data = await getPermissionsList();
        setPermissions(data);
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
      

      {permissionGroups.map((group, groupIndex) => (
        <div className="mb-5 border-bottom pb-3" key={groupIndex}>
          {editingGroupIndex === groupIndex ? (
            <>
              {/* Edit Mode */}
              <div className="mb-3">
                <label className="form-label">Group Name</label>
                <input
                  type="text"
                  className="form-control"
                  value={editGroupName}
                  onChange={(e) => setEditGroupName(e.target.value)}
                />
              </div>

              <div className="mb-2">
                <label className="form-label">Edit Permissions</label>
                <div className="row">
                  {editPermissions.map((perm, idx) => (
                    <div className="col-md-3 col-sm-6 mb-2" key={idx}>
                      <div className="d-flex align-items-center">
                        <input
                          type="checkbox"
                          className="form-check-input me-2"
                          checked
                          readOnly
                        />
                        <span>{perm}</span>
                        <button
                          className="btn btn-sm btn-danger ms-2"
                          onClick={() => handleRemoveEditPermission(perm)}
                        >
                          ✖
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              <div className="input-group mb-3">
                <input
                  type="text"
                  className="form-control"
                  placeholder="New permission"
                  value={editNewPermission}
                  onChange={(e) => setEditNewPermission(e.target.value)}
                />
                <button
                  className="btn btn-outline-primary"
                  onClick={handleAddEditPermission}
                >
                  + Add
                </button>
              </div>

              <div className="d-flex gap-2">
                <button className="btn btn-primary" onClick={handleSaveEditedGroup}>
                  Save
                </button>
                <button
                  className="btn btn-secondary"
                  onClick={() => setEditingGroupIndex(null)}
                >
                  Cancel
                </button>
              </div>
            </>
          ) : (
            <>
              {/* View Mode */}
              <div className="d-flex justify-content-between align-items-center mb-2">
                <h5 className="fw-semibold mb-0">{group.name}</h5>
                <div className="d-flex gap-2">
                
              
                </div>
              </div>

               <div>
  {permissions.map((group, groupIndex) => (
  <div key={groupIndex} className="mb-5 border-bottom pb-3">
    <div className="d-flex justify-content-between align-items-center mb-2">
      <h5 className="fw-semibold mb-0">{group.name}</h5>
      <div>
<button
  className="btn btn-sm btn-outline-secondary"
  data-bs-toggle="modal"
  data-bs-target="#add_role"
onClick={() => {
  setFormMode("edit");
  setEditGroupId(group.id);
  setNewGroupName(group.name);
  setNewPermissionInput("");

  // Don't map just names — keep full objects
  setNewPermissionList(group.children || []);
}}
>
  ✏️ Edit
</button>


      </div>
    </div>

    {/* Show edit UI if it's selected */}
    {editingGroupIndex === groupIndex + permissionGroups.length ? (
      <>
        <div className="mb-3">
          <label className="form-label">Group Name</label>
          <input
            type="text"
            className="form-control"
            value={editGroupName}
            onChange={(e) => setEditGroupName(e.target.value)}
          />
        </div>

        <div className="mb-2">
          <label className="form-label">Edit Permissions</label>
          <div className="row">
            {editPermissions.map((perm, idx) => (
              <div className="col-md-3 col-sm-6 mb-2" key={idx}>
                <div className="d-flex align-items-center">
                  <input
                    type="checkbox"
                    className="form-check-input me-2"
                    checked
                    readOnly
                  />
                  <span>{perm}</span>
                  <button
                    className="btn btn-sm btn-danger ms-2"
                    onClick={() => handleRemoveEditPermission(perm)}
                  >
                    ✖
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="input-group mb-3">
          <input
            type="text"
            className="form-control"
            placeholder="New permission"
            value={editNewPermission}
            onChange={(e) => setEditNewPermission(e.target.value)}
          />
          <button className="btn btn-outline-primary" onClick={handleAddEditPermission}>
            + Add
          </button>
        </div>

        <div className="d-flex gap-2">
          <button className="btn btn-primary" onClick={handleSaveEditedGroup}>
            Save
          </button>
          <button
            className="btn btn-secondary"
            onClick={() => setEditingGroupIndex(null)}
          >
            Cancel
          </button>
        </div>
      </>
    ) : (
      <div className="row">
        {group.children &&
          group.children
            .filter((perm) => !perm.name.toLowerCase().includes("delete"))
            .map((perm, permIndex) => (
              <div className="col-md-3 col-sm-6 mb-2" key={perm.id}>
                <div className="form-check">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    id={`group${groupIndex}-perm${permIndex}`}
                  />
                  <label
                    className="form-check-label"
                    htmlFor={`group${groupIndex}-perm${permIndex}`}
                  >
                    {perm.name}
                  </label>
                </div>
              </div>
            ))}
      </div>
    )}
  </div>
))}

    </div>
            </>
          )}
        </div>
      ))}

     

    
    </div>
</div>

            </div>
       
          </div>
        </div>
        
        <div className="modal fade" id="add_role">
          <div className="modal-dialog modal-dialog-centered">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">  {formMode === "add" ? "Add New Permission" : "Edit Permission Group"}</h4>
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
           {/* Add Group Form */}
    
        <div className="mt-4 border rounded p-3 bg-light">
       {/* <h4 className="modal-title">
  {formMode === "add" ? "Add New Permission" : "Edit Permission Group"}
</h4> */}


          <div className="mb-3">
            <label className="form-label">Permission Name</label>
            <input
              type="text"
              className="form-control"
              placeholder="e.g. Product Management"
              value={newGroupName}
              onChange={(e) => setNewGroupName(e.target.value)}
            />
          </div>

          <div className="mb-3">
            <label className="form-label">Add  Permissions</label>
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
        className="list-group-item py-1"
        key={perm.id ?? idx}
      >
        <input
          type="text"
          className="form-control"
          value={perm.name}
          onChange={(e) => {
            const updatedList = [...newPermissionList];
            updatedList[idx].name = e.target.value;
            setNewPermissionList(updatedList);
          }}
        />
      </li>
    ))}
  </ul>
)}

          </div>

          <div className="d-flex gap-2">
<button
  className="btn btn-primary"
  onClick={() => {
    if (formMode === "add") {
      handleAddPermissionGroup();
    } else {
      handleUpdatePermissionGroup();
    }
  }}
>
  Save
</button>


            <button
              className="btn btn-secondary"
              onClick={() => {
                setShowForm(false);
                setNewGroupName("");
                setNewPermissionInput("");
                setNewPermissionList([]);
              }}
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
