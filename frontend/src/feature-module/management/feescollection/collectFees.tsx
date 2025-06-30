import React, { useRef, useState } from "react";
import ImageWithBasePath from "../../../core/common/imageWithBasePath";
import {
  classduration,
  classSection,
  classSylabus,
  language,
  period,
  routinename,
  subjectGroup,
  teacher,
  Time,
  Timeto,
  paymentMethods,
  AdmissionNo,
  allClass,
  allSection,
  amount,
  DueDate,
  names,
  rollno,
} from "../../../core/common/selectoption/selectoption";
import { collectFessData } from "../../../core/data/json/collectFees";
import CommonSelect from "../../../core/common/commonSelect";
import { Link } from "react-router-dom";
import { all_routes } from "../../router/all_routes";
import TooltipOption from "../../../core/common/tooltipOption";
import PredefinedDateRanges from "../../../core/common/datePicker";
import { TableData } from "../../../core/data/interface";
import Table from "../../../core/common/dataTable/index";

type PDC = {
  bank: string;
  checkNo: string;
  date: string;
  amount: string;
};

type SelectedPDC = PDC & { index: number };

const CollectFees = () => {
  const routes = all_routes;
  const data = collectFessData;
  const dropdownMenuRef = useRef<HTMLDivElement | null>(null);
  const handleApplyClick = () => {
    if (dropdownMenuRef.current) {
      dropdownMenuRef.current.classList.remove("show");
    }
  };

// PDC Logic
 const [pdcList, setPdcList] = useState<PDC[]>([
    { bank: 'HDFC Bank', checkNo: '123456', date: '2025-07-01', amount: '10000' },
    { bank: 'ICICI Bank', checkNo: '223456', date: '2025-07-02', amount: '15000' },
    { bank: 'SBI Bank', checkNo: '323456', date: '2025-07-03', amount: '12000' }
  ]);
const amountOptions = [
  { label: '10,000', value: '10000' },
  { label: '15,000', value: '15000' },
  { label: '20,000', value: '20000' },
];


  const [selectedPdc, setSelectedPdc] = useState<SelectedPDC | null>(null);
  const [paymentMethod, setPaymentMethod] = useState<string>('Check');

  // extra form fields for Online/Cash
  const [receiptNo, setReceiptNo] = useState('');
  const [receiverBank, setReceiverBank] = useState('');
const [amount, setAmount] = useState('');

  const [remark, setRemark] = useState('');

  const handleRowClick = (index: number) => {
    setSelectedPdc({ ...pdcList[index], index });
    setPaymentMethod('Check');
    setReceiptNo('');
    setReceiverBank('');
    setAmount('');
    setRemark('');
  };

  const handlePaymentMethodChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const newMethod = e.target.value;

    if (newMethod !== 'Check' && selectedPdc) {
      if (window.confirm(`Are you sure you want to change payment method to ${newMethod}? This will remove the PDC entry.`)) {
        const updatedList = [...pdcList];
        updatedList.splice(selectedPdc.index, 1);
        setPdcList(updatedList);
        // PDC removed but form still shows below
      }
    }

    setPaymentMethod(newMethod);
  };


  const [newContents, setNewContents] = useState<number[]>([0]);
  const [tuesdayContents, settuesdayContents] = useState<number[]>([0]);
  const [wednessdayContents, setwednessdayContents] = useState<number[]>([0]);
  const [thursdayContents, setthursdayContents] = useState<number[]>([0]);
  const [fridayContents, setfridayContents] = useState<number[]>([0]);
  const addNewContent = () => {
    setNewContents([...newContents, newContents.length]);
  };
  const addTuesdayContent = () => {
    settuesdayContents([...tuesdayContents, tuesdayContents.length]);
  };
  const addwednessdayContent = () => {
    setwednessdayContents([...wednessdayContents, wednessdayContents.length]);
  };
  const addthursdayContents = () => {
    setthursdayContents([...thursdayContents, thursdayContents.length]);
  };
  const addfridayContents = () => {
    setfridayContents([...fridayContents, fridayContents.length]);
  };
  const removeContent = (index:any) => {
    setNewContents(newContents.filter((_, i) => i !== index));
  };
  const removetuesdayContent = (index:any) => {
    settuesdayContents(tuesdayContents.filter((_, i) => i !== index));
  };
  const  removewednessdayContent = (index:any) => {
    setwednessdayContents(wednessdayContents.filter((_, i) => i !== index));
  };
  const  removethursdayContents = (index:any) => {
    setthursdayContents(thursdayContents.filter((_, i) => i !== index));
  };
  const  removefridayContents = (index:any) => {
    setfridayContents(fridayContents.filter((_, i) => i !== index));
  };
    const columns = [
    {
      title: "Adm No",
      dataIndex: "admNo",
      render: (text: string) => (
        <Link to="#" className="link-primary">
          {text}
        </Link>
      ),
      sorter: (a: TableData, b: TableData) => a.admNo.length - b.admNo.length,
    },
    {
      title: "Roll No",
      dataIndex: "rollNo",
      sorter: (a: TableData, b: TableData) => a.rollNo.length - b.rollNo.length,
    },
    {
      title: "Student",
      dataIndex: "student",
      render: (text: string, record: any) => (
        <div className="d-flex align-items-center">
          <Link to={routes.studentDetail} className="avatar avatar-md">
            <ImageWithBasePath
              src={record.studentImage}
              className="img-fluid rounded-circle"
              alt="img"
            />
          </Link>
          <div className="ms-2">
            <p className="text-dark mb-0">
              <Link to={routes.studentDetail}>{text}</Link>
            </p>
            <span className="fs-12">{record.studentClass}</span>
          </div>
        </div>
      ),
      sorter: (a: TableData, b: TableData) =>
        a.student.length - b.student.length,
    },
    {
      title: "Class",
      dataIndex: "class",
      sorter: (a: TableData, b: TableData) => a.class.length - b.class.length,
    },
    {
      title: "Section",
      dataIndex: "section",
      sorter: (a: TableData, b: TableData) =>
        a.section.length - b.section.length,
    },
    {
      title: "Amount ($)",
      dataIndex: "amount",
      sorter: (a: TableData, b: TableData) => a.amount.length - b.amount.length,
    },

    {
      title: "Last Date",
      dataIndex: "lastDate",
      sorter: (a: TableData, b: TableData) =>
        a.lastDate.length - b.lastDate.length,
    },

    {
      title: "Status",
      dataIndex: "status",
      render: (text: string) => (
        <>
          {text === "Paid" ? (
            <span className="badge badge-soft-success d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              {text}
            </span>
          ) : (
            <span className="badge badge-soft-danger d-inline-flex align-items-center">
              <i className="ti ti-circle-filled fs-5 me-1"></i>
              {text}
            </span>
          )}
        </>
      ),
      sorter: (a: TableData, b: TableData) => a.status.length - b.status.length,
    },
    {
      title: "Action",
      dataIndex: "status",
      render: (text: string) => (
        <>
          {text === "Paid" ? (
            <Link to={routes.studentFees} className="btn btn-light">
              View Details
            </Link>
          ) : (
            <Link
              to="#"
              className="btn btn-light"
              data-bs-toggle="modal"
              data-bs-target="#add_fees_collect"
            >
              Collect Fees
            </Link>
          )}
        </>
      ),
    },
  ];
  
  return (
    <div>
      {/* Page Wrapper */}
      <div className="page-wrapper">
        <div className="content content-two">
          <div className="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div className="my-auto mb-2">
              <h3 className="page-title mb-1">Collect Fees</h3>
              <nav>
                <ol className="breadcrumb mb-0">
                  <li className="breadcrumb-item">
                    <Link to={routes.adminDashboard}>Dashboard</Link>
                  </li>
                  <li className="breadcrumb-item">Managment</li>
                  <li className="breadcrumb-item active" aria-current="page">
                 Collect Fees
                  </li>
                </ol>
              </nav>
            </div>
            <div className="d-flex my-xl-auto right-content align-items-center flex-wrap">
            <TooltipOption />
              <div className="mb-2">
                <Link
                  to="#"
                  className="btn btn-primary d-flex align-items-center"
                  data-bs-toggle="modal"
                  data-bs-target="#add_time_table"
                >
<i className="ti ti-cash me-2" />
Collect Fees


                </Link>
              </div>
            </div>
          </div>
          {/* Students List */}
          <div className="card">
            <div className="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
              <h4 className="mb-3">Fees List</h4>
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
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Admisson No</label>
                              <CommonSelect
                                className="select"
                                options={AdmissionNo}
                                defaultValue={AdmissionNo[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Roll No</label>
                              <CommonSelect
                                className="select"
                                options={rollno}
                                defaultValue={rollno[0]}
                              />
                            </div>
                          </div>

                          <div className="col-md-12">
                            <div className="mb-3">
                              <label className="form-label">Student</label>
                              <CommonSelect
                                className="select"
                                options={names}
                                defaultValue={names[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Class</label>
                              <CommonSelect
                                className="select"
                                options={allClass}
                                defaultValue={allClass[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-3">
                              <label className="form-label">Section</label>
                              <CommonSelect
                                className="select"
                                options={allSection}
                                defaultValue={allSection[0]}
                              />
                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-0">
                              <label className="form-label">Amount</label>
                       <CommonSelect
  className="select"
  options={amountOptions}
  defaultValue={amountOptions[0]}
/>

                            </div>
                          </div>
                          <div className="col-md-6">
                            <div className="mb-0">
                              <label className="form-label">Last Date</label>
                              <CommonSelect
                                className="select"
                                options={DueDate}
                                defaultValue={DueDate[0]}
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
                    Sort by A-Z{" "}
                  </Link>
                  <ul className="dropdown-menu p-3">
                    <li>
                      <Link to="#" className="dropdown-item rounded-1">
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
              {/* Student List */}
              <Table dataSource={data} columns={columns} Selection={true} />
              {/* /Student List */}
            </div>
          </div>
       
        </div>
      </div>
      {/* /Page Wrapper */}
      <>
        {/* Add Class Time Table */}
        <div className="modal fade" id="add_time_table">
          <div className="modal-dialog modal-dialog-centered modal-xl">
            <div className="modal-content">
              <div className="modal-header">
                <h4 className="modal-title">Collect Fees</h4>
                <button
                  type="button"
                  className="btn-close custom-btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                >
                  <i className="ti ti-x" />
                </button>
              </div>
              <form >
                <div className="modal-body">
                  <div className="row">
                  
            
                        <div className="col-lg-4">
                      <div className="mb-3">
                        <label className="form-label">Class</label>
                        <CommonSelect
                          className="select"
                          options={classSection}
                        />
                      </div>
                    </div>
                    <div className="col-lg-4">
                      <div className="mb-3">
                        <label className="form-label">Section</label>
                        <CommonSelect
                          className="select"
                          options={classSection}
                        />
                      </div>
                    </div>
                             <div className="col-lg-4">
                      <div className="mb-3">
                        <label className="form-label">Student Name</label>
                        <input type="text" className="form-control" />
                      </div>
                    </div>
                    <div className="col-lg-4">
                      <div className="mb-3">
                        <label className="form-label">Roll No.</label>
                        <CommonSelect
                          className="select"
                          options={subjectGroup}
                        />
                      </div>
                    </div>

                    
                    {/* Student Details */}
                    <div className="container px-2"> {/* Wrap for spacing and centering */}
  <div className="row bg-light rounded py-2 px-3 my-3 mx-auto" style={{ maxWidth: '95%' }}>
    {/* Student ID */}
    <div className="col-md-2 mb-2">
      <label className="form-label  mb-0">Student ID</label>
      <div className="">STU202501</div>
    </div>

    {/* Full Name */}
    <div className="col-md-3 mb-2">
      <label className="form-label  mb-0">Student Name</label>
      <div className="">Priyansh Desai</div>
    </div>

    {/* Paid Fees */}
    <div className="col-md-2 mb-2">
      <label className="form-label  mb-0">Paid Fees</label>
      <div className="text-success ">₹30,000</div>
    </div>

    {/* Unpaid Fees */}
    <div className="col-md-2 mb-2">
      <label className="form-label  mb-0">Unpaid Fees</label>
      <div className="text-danger">₹5,000</div>
    </div>

    {/* Total Amount */}
    <div className="col-md-3 mb-2">
      <label className="form-label  mb-0">Total Amount</label>
      <div className="text-primary ">₹35,000</div>
    </div>
  </div>
</div>
<br></br>



                  </div>
 <div className="container mt-4">
      <h5>PDC Table</h5>
      <div className="table-responsive">
        <table className="table table-bordered">
          <thead className="table-light">
            <tr>
              <th>Bank Name</th>
              <th>Check No.</th>
              <th>Date</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            {pdcList.map((item, idx) => (
              <tr key={idx} onClick={() => handleRowClick(idx)} style={{ cursor: 'pointer' }}>
                <td>{item.bank}</td>
                <td>{item.checkNo}</td>
                <td>{item.date}</td>
                <td>₹{item.amount}</td>
              </tr>
            ))}
            {pdcList.length === 0 && (
              <tr>
                <td colSpan={4} className="text-center text-muted">
                  No PDC entries.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      <hr />

      {selectedPdc && (
        <div className="bg-light p-4 rounded">
          <h5>Fill Payment Details</h5>
          <div className="row">
            {/* Bank Name, Check No, Date, Amount - always show */}
            <div className="col-md-3 mb-3">
              <label className="form-label">Bank Name</label>
              <input className="form-control" value={selectedPdc.bank} readOnly />
            </div>
            <div className="col-md-3 mb-3">
              <label className="form-label">Check No.</label>
              <input className="form-control" value={selectedPdc.checkNo} readOnly />
            </div>
            <div className="col-md-3 mb-3">
              <label className="form-label">Date</label>
              <input className="form-control" value={selectedPdc.date} readOnly />
            </div>
            <div className="col-md-3 mb-3">
              <label className="form-label">Amount</label>
              <input className="form-control" value={selectedPdc.amount} readOnly />
            </div>

            {/* Payment Method */}
            <div className="col-md-4 mb-3">
              <label className="form-label">Payment Method</label>
              <select
                className="form-select"
                value={paymentMethod}
                onChange={handlePaymentMethodChange}
              >
                <option value="Check">Check</option>
                <option value="Online">Online</option>
                <option value="Cash">Cash</option>
              </select>
            </div>

            {/* Show additional fields for Online */}
            {paymentMethod === 'Online' && (
              <>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Receipt No.</label>
                  <input className="form-control" value={receiptNo} onChange={(e) => setReceiptNo(e.target.value)} />
                </div>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Amount</label>
                  <input className="form-control" value={amount} onChange={(e) => setAmount(e.target.value)} />
                </div>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Receiver Bank</label>
                  <input className="form-control" value={receiverBank} onChange={(e) => setReceiverBank(e.target.value)} />
                </div>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Remark</label>
                  <input className="form-control" value={remark} onChange={(e) => setRemark(e.target.value)} />
                </div>
              </>
            )}

            {/* Show additional fields for Cash */}
            {paymentMethod === 'Cash' && (
              <>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Receipt No.</label>
                  <input className="form-control" value={receiptNo} onChange={(e) => setReceiptNo(e.target.value)} />
                </div>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Receiver Bank</label>
                  <input className="form-control" value={receiverBank} onChange={(e) => setReceiverBank(e.target.value)} />
                </div>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Amount</label>
<input className="form-control" value={amount} onChange={(e) => setAmount(e.target.value)} />




                </div>
                <div className="col-md-3 mb-3">
                  <label className="form-label">Remark</label>
                  <input className="form-control" value={remark} onChange={(e) => setRemark(e.target.value)} />
                </div>
              </>
            )}
          </div>
        </div>
      )}
    </div>    </div>
                <div className="modal-footer">
                  <Link
                    to="#"
                    className="btn btn-light me-2"
                    data-bs-dismiss="modal"
                  >
                    Cancel
                  </Link>
                  <Link
                    to="#"
                    className="btn btn-primary"
                    data-bs-dismiss="modal"
                  >
                    Complete
                  </Link>
                </div>
              </form>
            </div>
          </div>
        </div>
        {/* /Add Class Time Table */}

      </>
    </div>
  );
};

export default CollectFees;
