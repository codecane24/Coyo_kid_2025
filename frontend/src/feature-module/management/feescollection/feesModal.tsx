import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import CommonSelect from "../../../core/common/commonSelect";
import { feeGroup, feesTypes } from "../../../core/common/selectoption/selectoption";
import { DatePicker } from 'antd';
import dayjs from "dayjs";

interface FeesModalProps {
  editData?: {
    id: string;
    name: string;
    description: string;
    status: string;
  } | null;
  onAdd: (formData: { name: string; description: string; status: string }) => void;
  onEdit: (id: string, formData: { name: string; description: string; status: string }) => void;
  onDelete: (id: string) => void;
}

const FeesModal: React.FC<FeesModalProps> = ({ editData = null, onAdd, onEdit, onDelete }) => {
  const [activeContent, setActiveContent] = useState('');
  const [formData, setFormData] = useState({
    name: '',
    description: '',
    status: '1'
  });

  const handleContentChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setActiveContent(event.target.value);
  };

  useEffect(() => {
    if (editData) {
      setFormData({
        name: editData.name,
        description: editData.description,
        status: editData.status
      });
    } else {
      setFormData({
        name: '',
        description: '',
        status: '1'
      });
    }
  }, [editData]);

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

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleStatusChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData(prev => ({
      ...prev,
      status: e.target.checked ? "1" : "0"
    }));
  };

  const handleSubmitAdd = (e: React.FormEvent) => {
    e.preventDefault();
    onAdd(formData);
    setFormData({ name: '', description: '', status: '1' });
  };

  const handleSubmitEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editData?.id) {
      onEdit(editData.id, formData);
    }
  };

  const handleSubmitDelete = (e: React.FormEvent) => {
    e.preventDefault();
    if (editData?.id) {
      onDelete(editData.id);
    }
  };

  return (
    <>
      {/* Add Fees Group */}
      <div className="modal fade" id="add_fees_group">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header">
              <h4 className="modal-title">Add Fees Group</h4>
              <button
                type="button"
                className="btn-close custom-btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
                <i className="ti ti-x" />
              </button>
            </div>
            <form onSubmit={handleSubmitAdd}>
              <div className="modal-body">
                <div className="row">
                  <div className="col-md-12">
                    <div className="mb-3">
                      <label className="form-label">Fees Group</label>
                      <input
                        type="text"
                        className="form-control"
                        name="name"
                        placeholder="Enter Fees Group"
                        value={formData.name}
                        onChange={handleInputChange}
                        required
                      />
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Description</label>
                      <textarea
                        className="form-control"
                        name="description"
                        rows={4}
                        placeholder="Add Comment"
                        value={formData.description}
                        onChange={handleInputChange}
                      />
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
                          checked={formData.status === "1"}
                          onChange={handleStatusChange}
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div className="modal-footer">
                <Link
                  to="#"
                  className="btn btn-light me-2"
                  data-bs-dismiss="modal"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  className="btn btn-primary"
                  data-bs-dismiss="modal"
                >
                  Add Fees Group
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {/* Edit Fees Group */}
      <div className="modal fade" id="edit_fees_group">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header">
              <h4 className="modal-title">Edit Fees Group</h4>
              <button
                type="button"
                className="btn-close custom-btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
                <i className="ti ti-x" />
              </button>
            </div>
            <form onSubmit={handleSubmitEdit}>
              <div className="modal-body">
                <div className="row">
                  <div className="col-md-12">
                    <div className="mb-3">
                      <label className="form-label">Fees Group</label>
                      <input
                        type="text"
                        className="form-control"
                        name="name"
                        placeholder="Enter Fees Group"
                        value={formData.name}
                        onChange={handleInputChange}
                        required
                      />
                    </div>
                    <div className="mb-3">
                      <label className="form-label">Description</label>
                      <textarea
                        className="form-control"
                        name="description"
                        rows={4}
                        placeholder="Add Comment"
                        value={formData.description}
                        onChange={handleInputChange}
                      />
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
                          checked={formData.status === "1"}
                          onChange={handleStatusChange}
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div className="modal-footer">
                <Link
                  to="#"
                  className="btn btn-light me-2"
                  data-bs-dismiss="modal"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  className="btn btn-primary"
                  data-bs-dismiss="modal"
                >
                  Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {/* Delete Modal */}
      <div className="modal fade" id="delete-modal">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content">
            <form onSubmit={handleSubmitDelete}>
              <div className="modal-body text-center">
                <span className="delete-icon">
                  <i className="ti ti-trash-x" />
                </span>
                <h4>Confirm Deletion</h4>
                <p>
                  You want to delete this fees group. This can't be undone once you delete.
                </p>
                <div className="d-flex justify-content-center">
                  <Link
                    to="#"
                    className="btn btn-light me-3"
                    data-bs-dismiss="modal"
                  >
                    Cancel
                  </Link>
                  <button
                    type="submit"
                    className="btn btn-danger"
                    data-bs-dismiss="modal"
                  >
                    Yes, Delete
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </>
  );
};

export default FeesModal;