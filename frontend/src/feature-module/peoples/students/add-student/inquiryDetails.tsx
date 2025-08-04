import React, { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import axios from "axios";
import { Breadcrumb } from "react-bootstrap";
import { all_routes } from "../../../router/all_routes";

const InquiryDetails = () => {
  const { id } = useParams<{ id: string }>();
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
          <div>
            <h2 className="mb-1">
              Admission Inquiry Details
              <span className="badge bg-primary ms-3">
                Inquiry Code: {data.code}
              </span>
            </h2>
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
        </div>
        <div className="card">
          <div className="card-body">
            <h4 className="mb-3">Student Information</h4>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Name:</strong></div>
              <div className="col-md-8">{data.first_name} {data.middle_name} {data.last_name}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Academic Year:</strong></div>
              <div className="col-md-8">{data.academic_year}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Date of Enquiry:</strong></div>
              <div className="col-md-8">{data.date_of_enquiry?.slice(0, 10)}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Class:</strong></div>
              <div className="col-md-8">{data.class_id}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Gender:</strong></div>
              <div className="col-md-8">{data.gender}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Date of Birth:</strong></div>
              <div className="col-md-8">{data.date_of_birth?.slice(0, 10)}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Primary Contact:</strong></div>
              <div className="col-md-8">{data.primary_contact}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Email:</strong></div>
              <div className="col-md-8">{data.email}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Suitable Batch:</strong></div>
              <div className="col-md-8">{data.suitable_batch}</div>
            </div>
            <hr />
            <h4 className="mb-3">Father's Information</h4>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Name:</strong></div>
              <div className="col-md-8">{data.father_name}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Email:</strong></div>
              <div className="col-md-8">{data.father_email}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Phone:</strong></div>
              <div className="col-md-8">{data.father_phone}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Occupation:</strong></div>
              <div className="col-md-8">{data.father_occupation}</div>
            </div>
            <hr />
            <h4 className="mb-3">Mother's Information</h4>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Name:</strong></div>
              <div className="col-md-8">{data.mother_name}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Email:</strong></div>
              <div className="col-md-8">{data.mother_email}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Phone:</strong></div>
              <div className="col-md-8">{data.mother_phone}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Occupation:</strong></div>
              <div className="col-md-8">{data.mother_occupation}</div>
            </div>
            <hr />
            <h4 className="mb-3">Addresses</h4>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Permanent Address:</strong></div>
              <div className="col-md-8">
                {data.permanent_address?.address}, {data.permanent_address?.area}, {data.permanent_address?.city}, {data.permanent_address?.state}, {data.permanent_address?.pincode}
                <br />
                Landmark: {data.permanent_address?.landmark}
              </div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Current Address:</strong></div>
              <div className="col-md-8">
                {data.current_address?.address}, {data.current_address?.area}, {data.current_address?.city}, {data.current_address?.state}, {data.current_address?.pincode}
                <br />
                Landmark: {data.current_address?.landmark}
              </div>
            </div>
            <hr />
            <h4 className="mb-3">Other Details</h4>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Siblings (IDs):</strong></div>
              <div className="col-md-8">{Array.isArray(data.sibling_ids) ? data.sibling_ids.filter(Boolean).join(", ") : ""}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Status:</strong></div>
              <div className="col-md-8">{data.status}</div>
            </div>
            <div className="row mb-2">
              <div className="col-md-4"><strong>Created At:</strong></div>
              <div className="col-md-8">{data.created_at?.slice(0, 10)}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default InquiryDetails;