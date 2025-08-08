import React, { useState } from 'react';

type PDC = {
  id: number;
  method: 'Check' | 'Online' | 'Cash';
  bank?: string;
  checkNo?: string;
  date?: string;
  amount: string;
  referenceNo?: string;
  receiverBank?: string;
  remark?: string;
  clearanceTime?: string;
  receiptNo: string;
  isActive: boolean;
};

const PdcPaymentSection = () => {
  const [pdcList, setPdcList] = useState<PDC[]>([]);
  const [editingIndex, setEditingIndex] = useState<number | null>(null);

  // Common fields
  const [paymentMethod, setPaymentMethod] = useState<'Check' | 'Online' | 'Cash'>('Check');
  const [receiptNo, setReceiptNo] = useState('');
  const [clearanceTime, setClearanceTime] = useState('');
  const [remark, setRemark] = useState('');

  // Check fields
  const [bank, setBank] = useState('');
  const [checkNo, setCheckNo] = useState('');
  const [checkDate, setCheckDate] = useState('');
  const [checkAmount, setCheckAmount] = useState('');

  // Online fields
  const [referenceNo, setReferenceNo] = useState('');
  const [receiverBank, setReceiverBank] = useState('');
  const [onlineAmount, setOnlineAmount] = useState('');

  // Cash fields
  const [cashAmount, setCashAmount] = useState('');

  const resetForm = () => {
    setPaymentMethod('Check');
    setReceiptNo('');
    setClearanceTime('');
    setRemark('');
    setBank('');
    setCheckNo('');
    setCheckDate('');
    setCheckAmount('');
    setReferenceNo('');
    setReceiverBank('');
    setOnlineAmount('');
    setCashAmount('');
    setEditingIndex(null);
  };

  const handleSubmit = () => {
    const newPdc: PDC = {
      id: Date.now(),
      method: paymentMethod,
      receiptNo,
      clearanceTime,
      remark,
      amount:
        paymentMethod === 'Check'
          ? checkAmount
          : paymentMethod === 'Online'
          ? onlineAmount
          : cashAmount,
      isActive: true,
      ...(paymentMethod === 'Check' && {
        bank,
        checkNo,
        date: checkDate,
      }),
      ...(paymentMethod === 'Online' && {
        referenceNo,
        receiverBank,
      }),
    };

    if (editingIndex !== null) {
      const updatedList = [...pdcList];
      updatedList[editingIndex] = { ...newPdc, id: pdcList[editingIndex].id };
      setPdcList(updatedList);
    } else {
      setPdcList([...pdcList, newPdc]);
    }

    resetForm();
  };

  const handleEdit = (index: number) => {
    const pdc = pdcList[index];
    setEditingIndex(index);
    setPaymentMethod(pdc.method);
    setReceiptNo(pdc.receiptNo || '');
    setClearanceTime(pdc.clearanceTime || '');
    setRemark(pdc.remark || '');
    setBank(pdc.bank || '');
    setCheckNo(pdc.checkNo || '');
    setCheckDate(pdc.date || '');
    setCheckAmount(pdc.amount || '');
    setReferenceNo(pdc.referenceNo || '');
    setReceiverBank(pdc.receiverBank || '');
    setOnlineAmount(pdc.amount || '');
    setCashAmount(pdc.amount || '');
  };

  const handleClear = (index: number) => {
    const updated = [...pdcList];
    updated[index].isActive = false;
    setPdcList(updated);
  };

  const toggleActive = (index: number) => {
    const updated = [...pdcList];
    updated[index].isActive = !updated[index].isActive;
    setPdcList(updated);
  };

  return (
        <div className="page-wrapper">
             <div className="content content-two">
    <div className="container">
      <h5 className="mt-4">Fill Payment Details</h5>
      <div className="row bg-light p-3 rounded">
        {/* Payment Method Selector */}
        <div className="col-md-4 mb-3">
          <label className="form-label">Payment Method</label>
          <select
            className="form-select"
            value={paymentMethod}
            onChange={(e) => setPaymentMethod(e.target.value as any)}
          >
            <option value="Check">Check</option>
            <option value="Online">Online</option>
            <option value="Cash">Cash</option>
          </select>
        </div>

        <div className="col-md-4 mb-3">
          <label className="form-label">Receipt No.</label>
          <input
            className="form-control"
            value={receiptNo}
            onChange={(e) => setReceiptNo(e.target.value)}
          />
        </div>

        {paymentMethod === 'Check' && (
          <>
            <div className="col-md-4 mb-3">
              <label className="form-label">Bank Name</label>
              <input
                className="form-control"
                value={bank}
                onChange={(e) => setBank(e.target.value)}
              />
            </div>
            <div className="col-md-4 mb-3">
              <label className="form-label">Check No.</label>
              <input
                className="form-control"
                value={checkNo}
                onChange={(e) => setCheckNo(e.target.value)}
              />
            </div>
            <div className="col-md-4 mb-3">
              <label className="form-label">Date</label>
              <input
                type="date"
                className="form-control"
                value={checkDate}
                onChange={(e) => setCheckDate(e.target.value)}
              />
            </div>
            <div className="col-md-4 mb-3">
              <label className="form-label">Amount</label>
              <input
                className="form-control"
                value={checkAmount}
                onChange={(e) => setCheckAmount(e.target.value)}
              />
            </div>
          </>
        )}

        {paymentMethod === 'Online' && (
          <>
            <div className="col-md-4 mb-3">
              <label className="form-label">Reference No.</label>
              <input
                className="form-control"
                value={referenceNo}
                onChange={(e) => setReferenceNo(e.target.value)}
              />
            </div>
            <div className="col-md-4 mb-3">
              <label className="form-label">Receiver Bank</label>
              <input
                className="form-control"
                value={receiverBank}
                onChange={(e) => setReceiverBank(e.target.value)}
              />
            </div>
            <div className="col-md-4 mb-3">
              <label className="form-label">Amount</label>
              <input
                className="form-control"
                value={onlineAmount}
                onChange={(e) => setOnlineAmount(e.target.value)}
              />
            </div>
          </>
        )}

        {paymentMethod === 'Cash' && (
          <>
            <div className="col-md-4 mb-3">
              <label className="form-label">Amount</label>
              <input
                className="form-control"
                value={cashAmount}
                onChange={(e) => setCashAmount(e.target.value)}
              />
            </div>
          </>
        )}

        {(paymentMethod === 'Check' || paymentMethod === 'Online') && (
          <>
            <div className="col-md-4 mb-3">
              <label className="form-label">Clearance Time</label>
              <input
                type="date"
                className="form-control"
                value={clearanceTime}
                onChange={(e) => setClearanceTime(e.target.value)}
              />
            </div>
            <div className="col-md-4 mb-3">
              <label className="form-label">Remark</label>
              <input
                className="form-control"
                value={remark}
                onChange={(e) => setRemark(e.target.value)}
              />
            </div>
          </>
        )}

        <div className="text-end mt-3">
          <button className="btn btn-primary" onClick={handleSubmit}>
            {editingIndex !== null ? 'Update Entry' : 'Submit Payment'}
          </button>
        </div>
      </div>

    {/* PDC Table */}
{pdcList.length > 0 && (
  <div className="mt-4">
    <h5>PDC Records</h5>
    <table className="table table-bordered">
      <thead className="table-light">
        <tr>
          <th>Method</th>
          <th>Receipt No</th>
          <th>Amount</th>
          <th>Bank</th>
          <th>Check No</th>
          <th>Reference No</th>
          <th>Receiver Bank</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {pdcList.map((pdc, idx) => (
          <tr key={pdc.id}>
            <td>{pdc.method}</td>
            <td>{pdc.receiptNo}</td>
            <td>₹{pdc.amount}</td>
            <td>{pdc.method === 'Check' ? pdc.bank : '-'}</td>
            <td>{pdc.method === 'Check' ? pdc.checkNo : '-'}</td>
            <td>{pdc.method === 'Online' ? pdc.referenceNo : '-'}</td>
            <td>{pdc.method === 'Online' ? pdc.receiverBank : '-'}</td>
            <td>
              {pdc.isActive ? (
                <span className="badge bg-success">Active</span>
              ) : (
                <span className="badge bg-secondary">Inactive</span>
              )}
            </td>
            <td>
              <button
                className="btn btn-sm btn-primary me-2"
                onClick={() => handleEdit(idx)}
              >
                Edit
              </button>
              <button
                className="btn btn-sm btn-success me-2"
                onClick={() => handleClear(idx)}
              >
                Clear
              </button>
              <button
                className="btn btn-sm btn-warning"
                onClick={() => toggleActive(idx)}
              >
                {pdc.isActive ? 'Deactivate' : 'Activate'}
              </button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  </div>
)}

    </div></div></div>
  );
};

export default PdcPaymentSection;
