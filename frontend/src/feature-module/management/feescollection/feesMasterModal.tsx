import React, { useState } from "react";
import { Link } from "react-router-dom";
import CommonSelect from "../../../core/common/commonSelect";
import { feesTypes } from "../../../core/common/selectoption/selectoption";
import { DatePicker } from 'antd';
import dayjs from "dayjs";
import Toasts from "../../uiInterface/base-ui/toasts";
import { 
  createFeesMaster, 
  updateFeesMaster, 
  deleteFeesMaster,
  getFeesMasterById,
  getFeesGroupList 
} from "../../../services/FeesAllData";


// ...existing code for fetching feeGroupOptions if needed...

const FeesMasterModal = ({ feeGroupOptions = [] }) => {
  const [activeContent, setActiveContent] = useState('');
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  const formattedDate = `${month}-${day}-${year}`;
  const defaultValue = dayjs(formattedDate);

  const getModalContainer = () => {
    const modalElement = document.getElementById('modal-datepicker');
    return modalElement ? modalElement : document.body;
  };
  const getModalContainer2 = () => {
    const modalElement = document.getElementById('modal-datepicker2');
    return modalElement ? modalElement : document.body;
  };

  const handleContentChange = (event: any) => {
    setActiveContent(event.target.value);
  };

  return (
    <>
      {/* Add Fees Master */}
      <div className="modal fade" id="add_fees_master">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header">
              <div className="d-flex align-items-center">
                <h4 className="modal-title">Add Fees Master</h4>
                <span className="badge bg-soft-info ms-2">2024 - 2025</span>
              </div>
              <button
                type="button"
                className="btn-close custom-btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
                <i className="ti ti-x" />
              </button>
            </div>
            <form>
              <div className="modal-body" id="modal-datepicker2">
                <div className="row">
                  <div className="col-md-12">
                    <div className="col-md-6">
                      <div className="mb-3">
                        <label className="form-label">Fees Title</label>
                        <input type="text" className="form-control" />
                      </div>
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Fees Group</label>
                      <CommonSelect
                        className="select"
                        options={feeGroupOptions}
                        defaultValue={undefined}
                      />
                    </div>
                    {/* Fees Type */}
                    <div className="mb-3">
                      <label className="form-label">Fees Type</label>
                      <CommonSelect
                        className="select"
                        options={feesTypes}
                        defaultValue={undefined}
                      />
                    </div>
                  </div>
                  <div className="col-md-12">
                    <div className="row">
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Due Date</label>
                          <div className="date-pic">
                            <DatePicker
                              className="form-control datetimepicker"
                              format={{
                                format: "DD-MM-YYYY",
                                type: "mask",
                              }}
                              getPopupContainer={getModalContainer2}
                              defaultValue=""
                              placeholder="Select Date"
                            />
                            <span className="cal-icon">
                              <i className="ti ti-calendar" />
                            </span>
                          </div>
                        </div>
                      </div> 
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Amount</label>
                          <input type="text" className="form-control" />
                        </div>
                      </div>
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Fine Type</label>
                      <div className="d-flex align-items-center check-radio-group">
                        <label className="custom-radio">
                          <input type="radio" name="radio" value="" checked={activeContent === ''}
                                onChange={handleContentChange} />
                          <span className="checkmark" />
                          None
                        </label>
                        <label className="custom-radio fixed-radio">
                          <input type="radio" name="radio" value="fixed" onChange={handleContentChange} />
                          <span className="checkmark" />
                          Fixed
                        </label>
                        <label className="custom-radio percentage-radio">
                          <input type="radio" name="radio" value="percentage" onChange={handleContentChange} />
                          <span className="checkmark" />
                          Percentage
                        </label>
                      </div>
                    </div>
                    <div className={`percentage-field ${activeContent === 'percentage' ? 'percentage-field-show' : ''} `}>
                      <div className="row">
                        <div className="col-lg-6">
                          <div className="mb-3">
                            <label className="form-label">Percentage</label>
                            <input
                              type="text"
                              className="form-control"
                              placeholder="%"
                            />
                          </div>
                        </div>
                        <div className="col-lg-6">
                          <div className="mb-3">
                            <label className="form-label">Amount ($)</label>
                            <input
                              type="text"
                              className="form-control"
                              placeholder="$"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className={`fixed-field ${activeContent === 'fixed' ? 'fixed-field-show' : ''} `}>
                      <div className="row">
                        <div className="col-lg-12">
                          <div className="mb-3">
                            <label className="form-label">Amount ($)</label>
                            <input
                              type="text"
                              className="form-control"
                              placeholder="$"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div className="d-flex align-items-center justify-content-between">
                    <div className="status-title">
                      <h5>Status</h5>
                      <p>Change the Status by toggle </p>
                    </div>
                    <div className="form-check form-switch">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="switch-sm"
                      />
                    </div>
                  </div>
                </div>
              </div>
              <div className="modal-footer">
                <Link to="#" className="btn btn-light me-2" data-bs-dismiss="modal">
                  Cancel
                </Link>
                <Link to="#" data-bs-dismiss="modal" className="btn btn-primary">
                  Add Fees Master
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
      {/* Add Fees Master*/}
      {/* Edit Fees Master */}
      <div className="modal fade" id="edit_fees_master">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header">
              <div className="d-flex align-items-center">
                <h4 className="modal-title">Edit Fees Master</h4>
                <span className="badge bg-soft-info ms-2">2024 - 2025</span>
              </div>
              <button
                type="button"
                className="btn-close custom-btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
                <i className="ti ti-x" />
              </button>
            </div>
            <form>
              <div className="modal-body" id="modal-datepicker">
                <div className="row">
                  <div className="col-md-12">
                    <div className="mb-3">
                      <label className="form-label">Fees Group</label>
                      <CommonSelect
                        className="select"
                        options={feeGroupOptions}
                        defaultValue={feeGroupOptions[0]}
                      />
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Fees Type</label>
                      <CommonSelect
                        className="select"
                        options={feesTypes}
                        defaultValue={feesTypes[1]}
                      />
                    </div>
                  </div>
                  <div className="col-md-12">
                    <div className="row">
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Due Date</label>
                          <div className="date-pic">
                            <DatePicker
                              className="form-control datetimepicker"
                              format={{
                                format: "DD-MM-YYYY",
                                type: "mask",
                              }}
                              getPopupContainer={getModalContainer}
                              defaultValue={defaultValue}
                              placeholder="16 May 2024"
                            />
                            <span className="cal-icon">
                              <i className="ti ti-calendar" />
                            </span>
                          </div>
                        </div>
                      </div>
                      <div className="col-md-6">
                        <div className="mb-3">
                          <label className="form-label">Amount</label>
                          <input
                            type="text"
                            className="form-control"
                            placeholder="Enter Amout"
                            defaultValue="$500"
                          />
                        </div>
                      </div>
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Fine Type</label>
                      <div className="d-flex align-items-center check-radio-group">
                        <label className="custom-radio">
                          <input type="radio" name="radio" value="" checked={activeContent === ''}
                            onChange={handleContentChange} />
                          <span className="checkmark" />
                          None
                        </label>
                        <label className="custom-radio percentage-radio">
                          <input type="radio" name="radio" value="percentage" onChange={handleContentChange} />
                          <span className="checkmark" />
                          Percentage
                        </label>
                        <label className="custom-radio fixed-radio">
                          <input type="radio" name="radio" value="fixed" onChange={handleContentChange} />
                          <span className="checkmark" />
                          Fixed
                        </label>
                      </div>
                    </div>
                    <div className={`percentage-field ${activeContent === 'percentage' ? 'percentage-field-show' : ''} `}>
                      <div className="row">
                        <div className="col-lg-6">
                          <div className="mb-3">
                            <label className="form-label">Percentage</label>
                            <input
                              type="text"
                              className="form-control"
                              placeholder="%"
                            />
                          </div>
                        </div>
                        <div className="col-lg-6">
                          <div className="mb-3">
                            <label className="form-label">Amount ($)</label>
                            <input
                              type="text"
                              className="form-control"
                              placeholder="$"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className={`fixed-field ${activeContent === 'fixed' ? 'fixed-field-show' : ''} `}>
                      <div className="row">
                        <div className="col-lg-12">
                          <div className="mb-3">
                            <label className="form-label">Amount ($)</label>
                            <input
                              type="text"
                              className="form-control"
                              placeholder="$"
                            />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div className="d-flex align-items-center justify-content-between">
                    <div className="status-title">
                      <h5>Status</h5>
                      <p>Change the Status by toggle </p>
                    </div>
                    <div className="form-check form-switch">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="switch-sm2"
                      />
                    </div>
                  </div>
                </div>
              </div>
              <div className="modal-footer">
                <Link to="#" className="btn btn-light me-2" data-bs-dismiss="modal">
                  Cancel
                </Link>
                <Link to="#" data-bs-dismiss="modal" className="btn btn-primary">
                  Save Changes
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
      {/* Edit Fees Master*/}
    </>
  );
};

export default FeesMasterModal;
