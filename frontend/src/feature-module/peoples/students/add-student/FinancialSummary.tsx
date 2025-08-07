import React from "react";
import { Card, Table } from "react-bootstrap";

type FinancialSummaryProps = {
  studentName: string;
  studentCode: string;
  course: string;
  admissionDate: string;
};

const FinancialSummary: React.FC<FinancialSummaryProps> = ({
  studentName,
  studentCode,
  course,
  admissionDate,
}) => {
  // Static data for now
  const fees = [
    { label: "Tuition Fee", amount: 35000 },
    { label: "Library Fee", amount: 2000 },
    { label: "Lab Fee", amount: 3000 },
    { label: "Hostel Fee", amount: 15000 },
    { label: "Other Charges", amount: 1000 },
  ];

  const totalAmount = fees.reduce((sum, fee) => sum + fee.amount, 0);

  return (
<Card className="shadow-sm border-0">
  <Card.Header className="bg-light d-flex align-items-center py-3 px-4">
    <i className="ti ti-cash text-black me-2 fs-4" />
    <h5 className="mb-0">Financial Summary</h5>
  </Card.Header>

  <Card.Body className="px-4 py-4">
    <div className="row">
      {/* Student Details */}
      <div className="col-md-5 mb-4">
        <h6 className="mb-3 text-primary">Student Details</h6>
        <div className="mb-2">
          <strong>Name:</strong>
          <div className="text-muted">{studentName}</div>
        </div>
        <div className="mb-2">
          <strong>Student Code:</strong>
          <div className="text-muted">{studentCode}</div>
        </div>
        <div className="mb-2">
          <strong>Course:</strong>
          <div className="text-muted">{course}</div>
        </div>
        <div className="mb-2">
          <strong>Admission Date:</strong>
          <div className="text-muted">{admissionDate}</div>
        </div>
      </div>

      {/* Fee Breakdown */}
      <div className="col-md-7">
        <h6 className="mb-3 text-primary">Fee Breakdown</h6>
        <div className="table-responsive">
          <Table bordered hover className="mb-0">
            <thead className="table-light">
              <tr>
                <th>#</th>
                <th>Fee Component</th>
                <th>Amount (₹)</th>
              </tr>
            </thead>
            <tbody>
              {fees.map((fee, index) => (
                <tr key={index}>
                  <td>{index + 1}</td>
                  <td>{fee.label}</td>
                  <td>₹ {fee.amount.toLocaleString()}</td>
                </tr>
              ))}
              <tr className="table-success">
                <td colSpan={2}><strong>Total</strong></td>
                <td><strong>₹ {totalAmount.toLocaleString()}</strong></td>
              </tr>
            </tbody>
          </Table>
        </div>
      </div>
    </div>
  </Card.Body>
</Card>

  );
};

export default FinancialSummary;
