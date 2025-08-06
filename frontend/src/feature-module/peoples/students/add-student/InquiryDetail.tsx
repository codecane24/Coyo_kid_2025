import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";
import { Breadcrumb } from "react-bootstrap";
import { all_routes } from "../../../router/all_routes";

const InquiryDetail = () => {
  const { id } = useParams<{ id: string }>();
    const routes = all_routes;
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (id) {
      axios
        .get(`https://coyokid.abbangles.com/backend/api/v1/admission-inquiry/${id}`)
        .then((res) => {
          setData(res.data.data);
        })
        .catch(() => setData(null))
        .finally(() => setLoading(false));
    }
  }, [id]);

  if (loading) return <div className="p-4">Loading...</div>;
  if (!data) return <div className="p-4 text-danger">No data found.</div>;

  return (
    <div className="page-wrapper">
      <div className="content content-two">
   <div className="page-header d-flex align-items-center justify-content-between mb-4">
  {/* Left Side Content */}
  <div>
    <div className="d-flex align-items-center mb-2 gap-3 flex-wrap">
      <h2 className="mb-1">
        Admission Inquiry Details
      </h2>

      {/* Inquiry Code Badge */}
      <div
        className="px-2 py-1"
        style={{
          fontSize: "0.85rem",
          color: "#333",
          backgroundColor: "#E6F0FA",
          borderRadius: "6px",
          width: "fit-content",
        }}
      >
        <p className="mb-0">
          {data.code ? `Inquiry Code: ${data.code}` : "Loading..."}
        </p>
      </div>
    </div>

    {/* Breadcrumb */}
    <nav>
      <ol className="breadcrumb mb-0">
        <li className="breadcrumb-item">
          <Link to={all_routes.adminDashboard}>Dashboard</Link>
        </li>
        <li className="breadcrumb-item">
          <Link to={all_routes.admissionInquiryList}>Admission Inquiry List</Link>
        </li>
        <li className="breadcrumb-item active" aria-current="page">
          Inquiry Details
        </li>
      </ol>
    </nav>
  </div>

  {/* Right Side Buttons */}
  
  <div className="d-flex align-items-center gap-2">
    {/* Dropdown */}
    <select className="form-select" style={{ width: "150px" }}>
      <option value="">Select Option</option>
        <option value="3">Pick</option>
      <option value="1">Follow Up</option>
      <option value="2">Convert To Addmission</option>
      <option value="3">Archive</option>
    </select>

    {/* Edit Button */}
      {/* Icon-Only Edit Button */}
       <Link className="" to={routes.editAdmissionInquiry.replace(":id", String(id))}>
    <button className="btn btn-outline-primary p-2">
      <i className="ti ti-edit" style={{ fontSize: "1rem" }}></i>
    </button></Link>
  </div>
</div>


   <div className="card shadow-sm border-0">
  <div className="card-body">
    <h4 className="mb-4 border-bottom pb-2">Student Information</h4>

    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Name:</div>
      <div className="col-md-8 text-dark fw-medium">{data.first_name} {data.middle_name} {data.last_name}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Academic Year:</div>
      <div className="col-md-8 text-dark fw-medium">{data.academic_year}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Date of Enquiry:</div>
      <div className="col-md-8 text-dark fw-medium">{data.date_of_enquiry?.slice(0, 10)}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Class:</div>
      <div className="col-md-8 text-dark fw-medium">{data.class_id}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Gender:</div>
      <div className="col-md-8 text-dark fw-medium">{data.gender}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Date of Birth:</div>
      <div className="col-md-8 text-dark fw-medium">{data.date_of_birth?.slice(0, 10)}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Primary Contact:</div>
      <div className="col-md-8 text-dark fw-medium">{data.primary_contact}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Email:</div>
      <div className="col-md-8 text-dark fw-medium">{data.email}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Suitable Batch:</div>
      <div className="col-md-8 text-dark fw-medium">{data.suitable_batch}</div>
    </div>

    <hr className="my-4" />
    <h4 className="mb-4 border-bottom pb-2">Father's Information</h4>

    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Name:</div>
      <div className="col-md-8 text-dark fw-medium">{data.father_name}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Email:</div>
      <div className="col-md-8 text-dark fw-medium">{data.father_email}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Phone:</div>
      <div className="col-md-8 text-dark fw-medium">{data.father_phone}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Occupation:</div>
      <div className="col-md-8 text-dark fw-medium">{data.father_occupation}</div>
    </div>

    <hr className="my-4" />
    <h4 className="mb-4 border-bottom pb-2">Mother's Information</h4>

    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Name:</div>
      <div className="col-md-8 text-dark fw-medium">{data.mother_name}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Email:</div>
      <div className="col-md-8 text-dark fw-medium">{data.mother_email}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Phone:</div>
      <div className="col-md-8 text-dark fw-medium">{data.mother_phone}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Occupation:</div>
      <div className="col-md-8 text-dark fw-medium">{data.mother_occupation}</div>
    </div>

    <hr className="my-4" />
    <h4 className="mb-4 border-bottom pb-2">Addresses</h4>

    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Permanent Address:</div>
      <div className="col-md-8 text-dark fw-medium">
        {data.permanent_address?.address}, {data.permanent_address?.area}, {data.permanent_address?.city}, {data.permanent_address?.state}, {data.permanent_address?.pincode}<br />
        <span className="text-muted">Landmark: {data.permanent_address?.landmark}</span>
      </div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Current Address:</div>
      <div className="col-md-8 text-dark fw-medium">
        {data.current_address?.address}, {data.current_address?.area}, {data.current_address?.city}, {data.current_address?.state}, {data.current_address?.pincode}<br />
        <span className="text-muted">Landmark: {data.current_address?.landmark}</span>
      </div>
    </div>

    <hr className="my-4" />
    <h4 className="mb-4 border-bottom pb-2">Other Details</h4>

    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Siblings (IDs):</div>
      <div className="col-md-8 text-dark fw-medium">{Array.isArray(data.sibling_ids) ? data.sibling_ids.filter(Boolean).join(", ") : ""}</div>
    </div>
    <div className="row mb-3">
      <div className="col-md-4 fw-semibold text-muted">Status:</div>
      <div className="col-md-8 text-dark fw-medium">{data.status}</div>
    </div>
    <div className="row">
      <div className="col-md-4 fw-semibold text-muted">Created At:</div>
      <div className="col-md-8 text-dark fw-medium">{data.created_at?.slice(0, 10)}</div>
    </div>
  </div>
</div>

      </div>
    </div>
  );
};

export default InquiryDetail;