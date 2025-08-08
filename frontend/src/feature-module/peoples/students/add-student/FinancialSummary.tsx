import React, { useEffect, useState } from "react";
import { Card, Table } from "react-bootstrap";
import { getClassFeesByClassId, getClassFeesMasterById } from "../../../../services/FeesAllData";
import { toast } from "react-toastify";
import FinancialSummarySkeleton from "../../../../skeletons/FinancialSummarySkeleton";


type FinancialSummaryProps = {
  studentName: string;
  studentCode: string;
  course: string;
  admissionDate: string;
gotClassId:any;
  currentStep: number;
  setCurrentStep: React.Dispatch<React.SetStateAction<number>>;
  isEdit?: boolean;
};
interface Fee {
  label: string;
  amount: number;
}

const FinancialSummary: React.FC<FinancialSummaryProps> = ({
gotClassId,
  studentName,
  studentCode,
  course,
  admissionDate,
  currentStep,
  setCurrentStep,
  isEdit
}) => {

  const [fees, setFees] = useState<Fee[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [className, setClassName] = useState<any>(null);
  useEffect(() => {
  if (!gotClassId) return;

  const fetchFees = async () => {
    setLoading(true);
    try {
      const response = await getClassFeesByClassId(String(gotClassId));
      console.log("response:", response);
      console.log("response.data:", response.data);

      const apiDataArray = response.data;  // Correct here!
setClassName(response.data[0].class.name);

      if (Array.isArray(apiDataArray) && apiDataArray.length > 0) {
        const formattedFees = apiDataArray.map(item => ({
          label: item.feestype?.name || "N/A",
          amount: parseFloat(item.amount) || 0,
        }));
        setFees(formattedFees);
      } else {
        setFees([]);
        setError("No fees data found");
      }
    } catch (error) {
      console.error(error);
      setError("Failed to fetch fees");
      setFees([]);
    } finally {
      setLoading(false);
    }
  };

  fetchFees();
}, [gotClassId]);


  const totalAmount = fees.reduce((acc, fee) => acc + fee.amount, 0);
  
if (loading) return <FinancialSummarySkeleton/>;
if (error) return <p style={{ color: "red" }}>{error}</p>;
if (fees.length === 0) return <p>No fees available.</p>;


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
          <strong>Class:</strong>
          <div className="text-muted">{className}</div>
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
      <table className="table table-bordered table-hover mb-0">
        <thead className="table-light">
          <tr>
            <th>#</th>
            <th>Fee Type</th>
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
            <td colSpan={2}>
              <strong>Total</strong>
            </td>
            <td>
              <strong>₹ {totalAmount.toLocaleString()}</strong>
            </td>
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
    style={{ backgroundColor: "#003366", color: "#fff" }} // dark blue
   
  >
    <i className="bi bi-check-circle me-2" />
    Accept & Submit Form
  </button>
</Card>

  );
};

export default FinancialSummary;
