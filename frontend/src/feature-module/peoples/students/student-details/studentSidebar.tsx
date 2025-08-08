import React, { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import ImageWithBasePath from "../../../../core/common/imageWithBasePath";
import { getStudent, getStudentById } from "../../../../services/StudentData";
import StudentSidebarSkeleton from "../../../../skeletons/StudentSidebarSkeleton";

// Define the student type from API response
type Student = {
  id: number;
  code: string;
  first_name: string;
  middle_name?: string;
  last_name: string;
  dob?: string;
  gender?: string;
  blood_group?: string;
  phone?: string;
  email?: string;
  house?: string;
  religion?: string;
  category?: string;
  caste?: string;
  mother_tongue?: string;
  languages?: string; // JSON string
  profile_image?: string;
  roll_no?: string;
  status?: string;
};

const StudentSidebar = () => {
 const { id } = useParams<{ id: string }>();
  const [student, setStudent] = useState<Student | null>(null);
  const [loading, setLoading] = useState(true);
console.log(id)
useEffect(() => {
  if (id) {
    getStudentById(id)
      .then((response) => {
        if (response?.data?.step_1) {
          setStudent(response.data.step_1); // ✅ Now student is the correct object
        } else {
          console.error("Unexpected API shape:", response.data);
        }
      })
      .catch((error) => {
        console.error("❌ Failed to fetch student data:", error);
      })
      .finally(() => setLoading(false));
  }
}, [id]);
  if (loading) {
    return <StudentSidebarSkeleton/>;
  }

  if (!student) {
    return <p>No student found</p>;
  }

  // Convert dob to readable date
  const formattedDOB = student.dob
    ? new Date(student.dob).toLocaleDateString()
    : "N/A";

  // Parse languages JSON string if exists
  let languageList: string[] = [];
  try {
    languageList = student.languages ? JSON.parse(student.languages) : [];
  } catch {
    languageList = [];
  }

  return (
    <div className="col-xxl-3 col-xl-4 theiaStickySidebar">
      <div className="stickybar pb-4">
        <div className="card border-white">
          <div className="card-header">
            <div className="d-flex align-items-center flex-wrap row-gap-3">
              <div className="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                <ImageWithBasePath
                  src={student.profile_image || "assets/img/students/default.jpg"}
                  isApiImage={true}
                  className="img-fluid"
                  alt="student"
                />
              </div>
              <div className="overflow-hidden">
                <span className="badge badge-soft-success d-inline-flex align-items-center mb-1">
                  <i className="ti ti-circle-filled fs-5 me-1" />
                  {student.status === "2" ? "Active" : "Inactive"}
                </span>
                <h5 className="mb-1 text-truncate">
                  {student.first_name} {student.last_name}
                </h5>
                <p className="text-primary">{student.code}</p>
              </div>
            </div>
          </div>

          {/* Basic Information */}
          <div className="card-body">
            <h5 className="mb-3">Basic Information</h5>
            <dl className="row mb-0">
              <dt className="col-6 fw-medium text-dark mb-3">Roll No</dt>
              <dd className="col-6 mb-3">{student.roll_no || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Gender</dt>
              <dd className="col-6 mb-3">{student.gender || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Date Of Birth</dt>
              <dd className="col-6 mb-3">{formattedDOB}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Blood Group</dt>
              <dd className="col-6 mb-3">{student.blood_group || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">House</dt>
              <dd className="col-6 mb-3">{student.house || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Religion</dt>
              <dd className="col-6 mb-3">{student.religion || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Caste</dt>
              <dd className="col-6 mb-3">{student.caste || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Category</dt>
              <dd className="col-6 mb-3">{student.category || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Mother tongue</dt>
              <dd className="col-6 mb-3">{student.mother_tongue || "N/A"}</dd>
              <dt className="col-6 fw-medium text-dark mb-3">Languages</dt>
              <dd className="col-6 mb-3">
                {languageList.length > 0
                  ? languageList.map((lang, idx) => (
                      <span
                        key={idx}
                        className="badge badge-light text-dark me-2"
                      >
                        {lang}
                      </span>
                    ))
                  : "N/A"}
              </dd>
            </dl>
            <Link
              to="#"
              data-bs-toggle="modal"
              data-bs-target="#add_fees_collect"
              className="btn btn-primary btn-sm w-100"
            >
              Add Fees
            </Link>
          </div>
        </div>

        {/* Primary Contact Info */}
        <div className="card border-white">
          <div className="card-body">
            <h5 className="mb-3">Primary Contact Info</h5>
            <div className="d-flex align-items-center mb-3">
              <span className="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default">
                <i className="ti ti-phone" />
              </span>
              <div>
                <span className="text-dark fw-medium mb-1">Phone Number</span>
                <p>{student.phone || "N/A"}</p>
              </div>
            </div>
            <div className="d-flex align-items-center">
              <span className="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default">
                <i className="ti ti-mail" />
              </span>
              <div>
                <span className="text-dark fw-medium mb-1">Email Address</span>
                <p>{student.email || "N/A"}</p>
              </div>
            </div>
          </div>
        </div>
        {/* /Primary Contact Info */}
 {/* /Sibiling Information */}
        {/* Transport Information */}
        <div className="card border-white mb-0">
          <div className="card-body pb-1">
            <ul className="nav nav-tabs nav-tabs-bottom mb-3">
              <li className="nav-item">
                <Link
                  className="nav-link active"
                  to="#hostel"
                  data-bs-toggle="tab"
                >
                  Hostel
                </Link>
              </li>
              <li className="nav-item">
                <Link className="nav-link" to="#transport" data-bs-toggle="tab">
                  Transportation
                </Link>
              </li>
            </ul>
            <div className="tab-content">
              <div className="tab-pane fade show active" id="hostel">
                <div className="d-flex align-items-center mb-3">
                  <span className="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default">
                    <i className="ti ti-building-fortress fs-16" />
                  </span>
                  <div>
                    <h6 className="fs-14 mb-1">HI-Hostel, Floor</h6>
                    <p className="text-primary">No data</p>
                  </div>
                </div>
              </div>
              <div className="tab-pane fade" id="transport">
                <div className="d-flex align-items-center mb-3">
                  <span className="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default">
                    <i className="ti ti-bus fs-16" />
                  </span>
                  <div>
                    <span className="fs-12 mb-1">Route</span>
                    <p className="text-dark">No data</p>
                  </div>
                </div>
                <div className="row">
                  <div className="col-sm-6">
                    <div className="mb-3">
                      <span className="fs-12 mb-1">Bus Number</span>
                      <p className="text-dark">No data</p>
                    </div>
                  </div>
                  <div className="col-sm-6">
                    <div className="mb-3">
                      <span className="fs-12 mb-1">Pickup Point</span>
                      <p className="text-dark">No data</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        {/* /Transport Information */}
      </div>
    </div>
  );
};

export default StudentSidebar;
