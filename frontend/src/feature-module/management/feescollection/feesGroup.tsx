import React, { useRef, useState, useEffect } from "react";
import { all_routes } from "../../router/all_routes";
import { Link } from "react-router-dom";
import PredefinedDateRanges from "../../../core/common/datePicker";
import CommonSelect from "../../../core/common/commonSelect";
import { ids, names, status } from "../../../core/common/selectoption/selectoption";
import { TableData } from "../../../core/data/interface";
import Table from "../../../core/common/dataTable/index";
import { getFeesGroupList } from "../../../services/FeesAllData";
import FeesGroupModal from "./feesGroupModal";
import TooltipOption from "../../../core/common/tooltipOption";

const FeesGroup = () => {
  const routes = all_routes;
  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  const sortDropdownRef = useRef<HTMLUListElement | null>(null);
  const [data, setData] = useState<TableData[]>([]);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showAddModal, setShowAddModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [selectedFeeGroup, setSelectedFeeGroup] = useState<TableData | null>(null);
  const [showFilterDropdown, setShowFilterDropdown] = useState(false);
  const [showSortDropdown, setShowSortDropdown] = useState(false);

    // In parent component
  const [refreshTrigger, setRefreshTrigger] = useState(false);

  const refreshData = () => {
    setRefreshTrigger(prev => !prev);
  };


 useEffect(() => {
  const fetchFeesGroup = async () => {
    try {
      const res = await getFeesGroupList();
      setData(res?.data || []);
    } catch (err) {
      console.error("Failed to fetch fees groups:", err);
      setData([]);
    }
  };
  
  fetchFeesGroup();
}, [refreshTrigger]);

  const handleApplyClick = () => {
    setShowFilterDropdown(false);
  };

  const toggleFilterDropdown = () => {
    setShowFilterDropdown(!showFilterDropdown);
  };

  const toggleSortDropdown = () => {
    setShowSortDropdown(!showSortDropdown);
  };

  const handleEditClick = (record: TableData) => {
    setSelectedFeeGroup(record);
    setShowEditModal(true);
  };

  const handleDeleteClick = (record: TableData) => {
    setSelectedFeeGroup(record);
    setShowDeleteModal(true);
  };

  const columns = [
    {
      title: "ID",
      dataIndex: "id",
      render: (text: string) => (
        <Link to="#" className="link-primary">
          {text}
        </Link>
      ),
      sorter: (a: TableData, b: TableData) => a.id.length - b.id.length,
    },
    {
      title: "Fees Group",
      dataIndex: "name",
      sorter: (a: TableData, b: TableData) => a.name.length - b.name.length,
    },
    {
      title: "Description",
      dataIndex: "description",
      sorter: (a: TableData, b: TableData) => a.description.length - b.description.length,
    },
    {
      title: "Status",
      dataIndex: "status",
      render: (text: string) => (
        <>
          {text === "1" ? (
            <span className="badge badge-soft-success d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              Active
            </span>
          ) : (
            <span className="badge badge-soft-danger d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              Inactive
            </span>
          )}
        </>
      ),
      sorter: (a: TableData, b: TableData) => a.status.length - b.status.length,
    },
    {
      title: "Action",
      dataIndex: "action",
      render: (_: any, record: TableData) => (
        <div className="d-flex align-items-center">
          <div className="dropdown">
            <button
              className="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
              onClick={(e) => {
                e.stopPropagation();
                setSelectedFeeGroup(record);
              }}
            >
              <i className="ti ti-dots-vertical fs-14" />
            </button>
            <div className={`dropdown-menu dropdown-menu-right p-3 ${selectedFeeGroup?.id === record.id ? 'show' : ''}`}>
              <button
                className="dropdown-item rounded-1"
                onClick={() => handleEditClick(record)}
              >
                <i className="ti ti-edit-circle me-2" />
                Edit
              </button>
              <button
                className="dropdown-item rounded-1"
                onClick={() => handleDeleteClick(record)}
              >
                <i className="ti ti-trash-x me-2" />
                Delete
              </button>
            </div>
          </div>
        </div>
      ),
    },
  ];

  return (
    <>
      <div className="page-wrapper">
        <div className="content">
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Fees Collection</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">
                    <Link to="#">Fees Collection</Link>
                  </li>
                  <li className="breadcrumb-item active" aria-current="page">
                    Fees Group
                  </li>
                </ol>
              </nav>
            </div>
            <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
              <TooltipOption />
              <div className="mb-2">
                <button
                  className="btn btn-primary"
                  onClick={() => setShowAddModal(true)}
                >
                  <i className="ti ti-square-rounded-plus me-2" />
                  Add Fees Group
                </button>
              </div>
            </div>
          </div>
          
          <div className="card">
            <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
              <h4 className="mb-3">Fees Collection</h4>
              <div className="d-flex align-items-center flex-wrap">
                <div className="input-icon-start mb-3 me-2 position-relative">
                  <PredefinedDateRanges/>
                </div>
                <div className="dropdown mb-3 me-2">
                  <button
                    className="btn btn-outline-light bg-white dropdown-toggle"
                    onClick={toggleFilterDropdown}
                  >
                    <i className="ti ti-filter me-2" />
                    Filter
                  </button>
                  <div className={`dropdown-menu drop-width ${showFilterDropdown ? 'show' : ''}`} ref={dropdownMenuRef}>
                    <form>
                      <div className="d-flex align-items-center border-bottom p-3">
                        <h4>Filter</h4>
                      </div>
                      <div className="p-3 border-bottom">
                        <div className="row">
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">ID</label>
                              <CommonSelect
                                className="select"
                                options={ids}
                                defaultValue={ids[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Name</label>
                              <CommonSelect
                                className="select"
                                options={names}
                                defaultValue={names[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-12">
                            <div className="mb-0">
                              <label className="form-label">Status</label>
                              <CommonSelect
                                className="select"
                                options={status}
                                defaultValue={status[0]}
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                      <div className="p-3 d-flex align-items-center justify-content-end">
                        <button 
                          type="button"
                          className="btn btn-light me-3"
                          onClick={() => setShowFilterDropdown(false)}
                        >
                          Reset
                        </button>
                        <button
                          type="button"
                          className="btn btn-primary"
                          onClick={handleApplyClick}
                        >
                          Apply
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
                <div className="dropdown mb-3">
                  <button
                    className="btn btn-outline-light bg-white dropdown-toggle"
                    onClick={toggleSortDropdown}
                  >
                    <i className="ti ti-sort-ascending-2 me-2" />
                    Sort by A-Z
                  </button>
                  <ul className={`dropdown-menu p-3 ${showSortDropdown ? 'show' : ''}`} ref={sortDropdownRef}>
                    <li>
                      <button className="dropdown-item rounded-1">
                        Ascending
                      </button>
                    </li>
                    <li>
                      <button className="dropdown-item rounded-1">
                        Descending
                      </button>
                    </li>
                    <li>
                      <button className="dropdown-item rounded-1">
                        Recently Viewed
                      </button>
                    </li>
                    <li>
                      <button className="dropdown-item rounded-1">
                        Recently Added
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div className="card-body p-0 py-3">
              <Table dataSource={data} columns={columns} Selection={true} />
            </div>
          </div>
        </div>
      </div>

      <FeesGroupModal
        feeGroupToEdit={selectedFeeGroup}
        showEditModal={showEditModal}
        onClose={() => setShowEditModal(false)}
        showAddModal={showAddModal}
        onAddClose={() => setShowAddModal(false)}
        showDeleteModal={showDeleteModal}
        onDeleteClose={() => setShowDeleteModal(false)}
        refreshData={refreshData}
      />
    </>
  );
};

export default FeesGroup;