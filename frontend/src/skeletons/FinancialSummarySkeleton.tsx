import React from "react";
import Skeleton from "react-loading-skeleton";
import 'react-loading-skeleton/dist/skeleton.css';
import { Card } from "react-bootstrap";

const FinancialSummarySkeleton: React.FC = () => {
  return (
    <Card className="shadow-sm border-0">
      <Card.Header className="bg-light d-flex align-items-center py-3 px-4">
        <i className="ti ti-cash text-black me-2 fs-4" />
        <h5 className="mb-0">
          <Skeleton width={120} height={24} />
        </h5>
      </Card.Header>

      <Card.Body className="px-4 py-4">
        <div className="row">
          {/* Student Details */}
          <div className="col-md-5 mb-4">
            <h6 className="mb-3 text-primary">
              <Skeleton width={100} height={20} />
            </h6>
            {[...Array(4)].map((_, i) => (
              <div className="mb-2" key={i}>
                <strong>
                  <Skeleton width={80} height={18} />
                </strong>
                <div className="text-muted">
                  <Skeleton width={140} height={16} />
                </div>
              </div>
            ))}
          </div>

          {/* Fee Breakdown */}
          <div className="col-md-7">
            <h6 className="mb-3 text-primary">
              <Skeleton width={130} height={20} />
            </h6>
            <div className="table-responsive">
              <table className="table table-bordered table-hover mb-0">
                <thead className="table-light">
                  <tr>
                    <th><Skeleton width={20} /></th>
                    <th><Skeleton width={100} /></th>
                    <th><Skeleton width={80} /></th>
                  </tr>
                </thead>
                <tbody>
                  {[...Array(4)].map((_, index) => (
                    <tr key={index}>
                      <td><Skeleton width={20} /></td>
                      <td><Skeleton width={120} /></td>
                      <td><Skeleton width={80} /></td>
                    </tr>
                  ))}
                  <tr className="table-success">
                    <td colSpan={2}><strong><Skeleton width={80} /></strong></td>
                    <td><strong><Skeleton width={80} /></strong></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </Card.Body>

      <button
        type="submit"
        className="btn"
        style={{ backgroundColor: "#003366", color: "#fff" }}
        disabled
      >
        <i className="bi bi-check-circle me-2" />
        <Skeleton width={130} height={20} />
      </button>
    </Card>
  );
};

export default FinancialSummarySkeleton;
