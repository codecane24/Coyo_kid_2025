// src/components/Skeletons/StudentSidebarSkeleton.tsx
import React from "react";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const StudentSidebarSkeleton: React.FC = () => {
  return (
    <div className="col-xxl-3 col-xl-4 theiaStickySidebar">
      <div className="stickybar pb-4">
        {/* Profile Card */}
        <div className="card border-white">
          <div className="card-header">
            <div className="d-flex align-items-center flex-wrap row-gap-3">
              <div className="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                <Skeleton circle height={80} width={80} />
              </div>
              <div className="overflow-hidden">
                <Skeleton width={80} height={20} className="mb-1" />
                <Skeleton width={120} height={22} />
                <Skeleton width={100} height={18} />
              </div>
            </div>
          </div>

          {/* Basic Information */}
          <div className="card-body">
            <h5 className="mb-3">Basic Information</h5>
            <dl className="row mb-0">
              {Array.from({ length: 10 }).map((_, i) => (
                <React.Fragment key={i}>
                  <dt className="col-6 fw-medium text-dark mb-3">
                    <Skeleton width={80} />
                  </dt>
                  <dd className="col-6 mb-3">
                    <Skeleton width={100} />
                  </dd>
                </React.Fragment>
              ))}
            </dl>
            <Skeleton height={35} />
          </div>
        </div>
      </div>
    </div>
  );
};

export default StudentSidebarSkeleton;
