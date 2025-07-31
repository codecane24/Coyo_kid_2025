import React, { useState } from "react";

type PaymentMethod = "cash" | "online" | "cheque";

interface Entry {
  method: PaymentMethod;
  date: string;
  receiverBank: string;
  amount: string;
  receiptNo: string;
  remarks: string;
  referenceNo?: string;
  bankName?: string;
  chequeNo?: string;
}

const FeesManagement: React.FC = () => {
  const [entries, setEntries] = useState<Entry[]>([
    {
      method: "cash",
      date: "",
      receiverBank: "",
      amount: "",
      receiptNo: "",
      remarks: "",
    },
  ]);

  const handleChange = (index: number, field: keyof Entry, value: string) => {
    const updated = [...entries];
    updated[index][field] = value as any;
    setEntries(updated);
  };

  const addEntry = () => {
    setEntries([
      ...entries,
      {
        method: "cash",
        date: "",
        receiverBank: "",
        amount: "",
        receiptNo: "",
        remarks: "",
      },
    ]);
  };

  const removeEntry = (index: number) => {
    const updated = [...entries];
    updated.splice(index, 1);
    setEntries(updated);
  };

  const handleSubmit = () => {
    console.log("Submitting Financial Details:", entries);
    alert("Submitted Successfully! 🚀");
  };

  return (
         <div className="page-wrapper">
    <div className="card mt-4">
      <div className="card-header bg-light">
        <div className="d-flex align-items-center">
          <span className="avatar avatar-sm bg-white text-dark d-flex align-items-center justify-content-center me-2">
            <i className="bi bi-cash-coin fs-5"></i>
          </span>
          <h5 className="mb-0 text-dark">Fees Management</h5>
        </div>
      </div>

      <div className="card-body">
        {entries.map((entry, idx) => (
          <div className="mb-4 border rounded p-3 bg-light-subtle" key={idx}>
            <h6 className="mb-3 text-primary fw-semibold d-flex align-items-center">
              <i className="bi bi-wallet2 me-2"></i> Payment Entry #{idx + 1}
            </h6>

            <div className="row g-3">
              <div className="col-md-4">
                <label className="form-label">Payment Method</label>
                <div className="input-group">
                  <span className="input-group-text"><i className="bi bi-credit-card" /></span>
                  <select
                    className="form-select"
                    value={entry.method}
                    onChange={(e) => handleChange(idx, "method", e.target.value)}
                  >
                    <option value="cash">Cash</option>
                    <option value="online">Online</option>
                    <option value="cheque">Cheque</option>
                  </select>
                </div>
              </div>

              <div className="col-md-4">
                <label className="form-label">Date</label>
                <div className="input-group">
                  <span className="input-group-text"><i className="bi bi-calendar-date" /></span>
                  <input
                    type="date"
                    className="form-control"
                    value={entry.date}
                    onChange={(e) => handleChange(idx, "date", e.target.value)}
                  />
                </div>
              </div>

              <div className="col-md-4">
                <label className="form-label">Receiver Bank</label>
                <div className="input-group">
                  <span className="input-group-text"><i className="bi bi-bank2" /></span>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="e.g. HDFC Bank"
                    value={entry.receiverBank}
                    onChange={(e) => handleChange(idx, "receiverBank", e.target.value)}
                  />
                </div>
              </div>

              <div className="col-md-4">
                <label className="form-label">Amount</label>
                <div className="input-group">
                  <span className="input-group-text">₹</span>
                  <input
                    type="number"
                    className="form-control"
                    placeholder="Enter amount"
                    value={entry.amount}
                    onChange={(e) => handleChange(idx, "amount", e.target.value)}
                  />
                </div>
              </div>

              <div className="col-md-4">
                <label className="form-label">Receipt No</label>
                <div className="input-group">
                  <span className="input-group-text"><i className="bi bi-receipt-cutoff" /></span>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Receipt Number"
                    value={entry.receiptNo}
                    onChange={(e) => handleChange(idx, "receiptNo", e.target.value)}
                  />
                </div>
              </div>

              <div className="col-md-4">
                <label className="form-label">Remarks</label>
                <div className="input-group">
                  <span className="input-group-text"><i className="bi bi-chat-left-text" /></span>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Remarks"
                    value={entry.remarks}
                    onChange={(e) => handleChange(idx, "remarks", e.target.value)}
                  />
                </div>
              </div>

              {entry.method === "online" && (
                <div className="col-md-4">
                  <label className="form-label">Reference No</label>
                  <div className="input-group">
                    <span className="input-group-text"><i className="bi bi-hash" /></span>
                    <input
                      type="text"
                      className="form-control"
                      placeholder="Reference No"
                      value={entry.referenceNo || ""}
                      onChange={(e) => handleChange(idx, "referenceNo", e.target.value)}
                    />
                  </div>
                </div>
              )}

              {entry.method === "cheque" && (
                <>
                  <div className="col-md-4">
                    <label className="form-label">Bank Name</label>
                    <div className="input-group">
                      <span className="input-group-text"><i className="bi bi-bank" /></span>
                      <input
                        type="text"
                        className="form-control"
                        placeholder="Cheque Bank"
                        value={entry.bankName || ""}
                        onChange={(e) => handleChange(idx, "bankName", e.target.value)}
                      />
                    </div>
                  </div>

                  <div className="col-md-4">
                    <label className="form-label">Cheque No</label>
                    <div className="input-group">
                      <span className="input-group-text"><i className="bi bi-upc-scan" /></span>
                      <input
                        type="text"
                        className="form-control"
                        placeholder="Cheque Number"
                        value={entry.chequeNo || ""}
                        onChange={(e) => handleChange(idx, "chequeNo", e.target.value)}
                      />
                    </div>
                  </div>
                </>
              )}
            </div>
          </div>
        ))}

        <div className="d-flex justify-content-between align-items-center mt-4">
          <div>
            <button
              type="button"
              className="btn btn-outline-danger me-2"
              onClick={() => removeEntry(entries.length - 1)}
              disabled={entries.length === 1}
            >
              <i className="bi bi-trash3 me-1" /> Delete Last
            </button>
            <button
              type="button"
              className="btn btn-outline-primary"
              onClick={addEntry}
            >
              <i className="bi bi-plus-circle me-1" /> Add Payment Entry
            </button>
          </div>

          <div>
            <button
              type="submit"
              className="btn"
              style={{ backgroundColor: "#003366", color: "#fff" }}
              onClick={handleSubmit}
            >
              <i className="bi bi-check-circle me-2" />
              Submit Form
            </button>
          </div>
        </div>
      </div>
    </div></div>
  );
};

export default FeesManagement;
