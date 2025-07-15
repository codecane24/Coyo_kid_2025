import React, { useRef,useEffect,useState } from "react";
import { Link } from "react-router-dom";
import Table from "../../core/common/dataTable/index";
import { manageusersData } from "../../core/data/json/manageuser";
import { TableData } from "../../core/data/interface";
import PredefinedDateRanges from "../../core/common/datePicker";
import CommonSelect from "../../core/common/commonSelect";
import { Reason } from "../../core/common/selectoption/selectoption";
import { all_routes } from "../router/all_routes";
import TooltipOption from "../../core/common/tooltipOption";
import { getUser } from "../../services/UserData";
import Datatable from "../../core/common/dataTable/index";

type User = {
  id: string;
  name: string;
  class: string;
  section: string;
  dateOfJoined: string;
  status: string;
};


const Manageusers = () => {
  const routes = all_routes;

  // const data = manageusersData;
  const [data, setData] = useState<User[]>([]);
  // const safeText = (text: string | undefined | null) => text?.trim() || "N/A";

  // ✅ Fetch user data from API
useEffect(() => {
  const fetchUsers = async () => {
    try {
      const response = await getUser();
      console.log("✅ API response: ", response);
      console.log("✅ response.data: ", response?.data);

   setData(
  (response?.data || []).map((item: any) => ({
    ...item,
    key: item.id, // 👈 Required by Ant Design Table
  }))
);
;
    } catch (error) {
      console.error("❌ API failed: ", error);
    }
  };

  fetchUsers();
  console.log(fetchUsers())
}, []);




  // ✅ Columns config
const safeText = (text: string | null | undefined) => text?.trim() || "N/A";

const columns = [
  {
    title: "ID",
    dataIndex: "id",
    render: (_: string, record: any) => (
      <Link to="#" className="link-primary">
        {safeText(record.id?.toString())}
      </Link>
    ),
    sorter: (a: any, b: any) => a.id - b.id,
  },
  {
    title: "Name",
    dataIndex: "name",
    render: (text: string) => safeText(text),
    sorter: (a: any, b: any) => a.name?.localeCompare(b.name),
  },
  {
    title: "Email",
    dataIndex: "email",
    render: (text: string) => safeText(text),
    sorter: (a: any, b: any) => a.email?.localeCompare(b.email),
  },
  {
    title: "Mobile",
    dataIndex: "mobile_number",
    render: (text: string) => safeText(text),
    sorter: (a: any, b: any) => a.mobile_number?.localeCompare(b.mobile),
  },
  {
    title: "Code",
    dataIndex: "code",
    render: (text: string) => safeText(text),
    sorter: (a: any, b: any) => a.code?.localeCompare(b.code),
  },
  {
    title: "Status",
    dataIndex: "status",
    render: (text: string) => {
      const status = safeText(text);
      return status.toLowerCase() === "active" ? (
        <span className="badge badge-soft-success d-inline-flex align-items-center">
          <i className="ti ti-circle-filled fs-5 me-1"></i>
          {status}
        </span>
      ) : (
        <span className="badge badge-soft-danger d-inline-flex align-items-center">
          <i className="ti ti-circle-filled fs-5 me-1"></i>
          {status}
        </span>
      );
    },
    sorter: (a: any, b: any) => a.status?.localeCompare(b.status),
  },
 {
  title: "Action",
  dataIndex: "action",
  render: (_: any, record: any) => (
    <div className="d-flex align-items-center">
      <div className="dropdown">
        <Link
          to="#"
          className="btn btn-white btn-icon btn-sm d-flex align-items-center justify-content-center rounded-circle p-0"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <i className="ti ti-dots-vertical fs-14" />
        </Link>
        <ul className="dropdown-menu dropdown-menu-right p-3">
          <li>
            <Link className="dropdown-item rounded-1" to="#">
              <i className="ti ti-trash-x me-2" />
              Delete
            </Link>
          </li>
          <li>
        <Link
  className="dropdown-item rounded-1"
  to={`/add-user/${record.encryptid}`} // ✅ id goes in URL
>
  <i className="ti ti-edit me-2" />
  Edit
</Link>

          </li>
        </ul>
      </div>
    </div>
  ),
},

  {
  title: "Profile",
  dataIndex: "profile_image",
  render: (url: string) => (
    <img src={url} alt="Profile" style={{ width: 40, height: 40, borderRadius: "50%" }} />
  ),
}

];




  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };

  return (
    <div>
      <>

        {/* Page Wrapper */}
        <div className="page-wrapper">
          <div className="content">
            {/* Page Header */}
            <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
              <div className="my-auto mb-2">
                <h3 className="page-title mb-1">Users</h3>
                <nav>
                  <ol className="breadcrumb mb-0">
                    <li className="breadcrumb-item">
                      <Link to={routes.adminDashboard}>Dashboard</Link>
                    </li>
                    <li className="breadcrumb-item">
                      <Link to="#">User Management</Link>
                    </li>
                    <li className="breadcrumb-item active" aria-current="page">
                      Users
                    </li>
                  </ol>
                </nav>
              </div>
              <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
              <TooltipOption />
                <div className="mb-2">
                  <Link
                    to="/user-management/add-users"
                    className="btn btn-primary d-flex align-items-center"
                  >
                    <i className="ti ti-square-rounded-plus me-2" />
                    Add User
                  </Link>
                </div>
              </div>
            </div>
            {/* /Page Header */}
            {/* Filter Section */}
            <div className="card">
              <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
                <h4 className="mb-3">Users List</h4>
                <div className="d-flex align-items-center flex-wrap">
                  <div className="input-icon-start mb-3 me-2 position-relative">
                    <PredefinedDateRanges />
                  </div>
                  <div className="dropdown mb-3 me-2">
                    <Link
                      to="#"
                      className="btn btn-outline-light bg-white dropdown-toggle"
                      data-bs-toggle="dropdown"
                      data-bs-auto-close="outside"
                    >
                      <i className="ti ti-filter me-2" />
                      Filter
                    </Link>
                    <div
                      className="dropdown-menu drop-width"
                      ref={dropdownMenuRef}
                    >
                      <form>
                        <div className="d-flex align-items-center border-bottom p-3">
                          <h4>Filter</h4>
                        </div>
                        <div className="p-3 border-bottom">
                          <div className="row">
                            <div className="col-md-12">
                              <div className="mb-0">
                                <label className="form-label">Users</label>
                                <CommonSelect
                                  className="select"
                                  options={Reason}
                                />
                              </div>
                            </div>
                          </div>
                        </div>
                        <div className="p-3 d-flex align-items-center justify-content-end">
                          <Link to="#" className="btn btn-light me-3">
                            Reset
                          </Link>
                          <Link
                            to="#"
                            className="btn btn-primary"
                            onClick={handleApplyClick}
                          >
                            Apply
                          </Link>
                        </div>
                      </form>
                    </div>
                  </div>
                  <div className="dropdown mb-3">
                    <Link
                      to="#"
                      className="btn btn-outline-light bg-white dropdown-toggle"
                      data-bs-toggle="dropdown"
                    >
                      <i className="ti ti-sort-ascending-2 me-2" />
                      Sort by A-Z
                    </Link>
                    <ul className="dropdown-menu p-3">
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
              {/* User List */}
              <div className="card-body p-0 py-3">
 <Table
  columns={columns}
  dataSource={data}

/>



              </div>
              {/* /User List */}
            </div>
            {/* /Filter Section */}
            <div className="row align-items-center">
              <div className="col-md-12">
                <div className="datatable-paginate mt-4" />
              </div>
            </div>
          </div>
        </div>
        {/* /Page Wrapper */}
      </>
    </div>
  );
};

export default Manageusers;
