import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import CommonSelect from "../../../core/common/commonSelect";
import { feeGroup, feesTypes } from "../../../core/common/selectoption/selectoption";

interface FeesGroupModalProps {
  feeGroupToEdit?: {
    id?: string;
    name?: string;
    description?: string;
    status?: string;
  } | null;
  showEditModal: boolean;
  onClose: () => void;
  showAddModal: boolean;
  onAddClose: () => void;
  showDeleteModal: boolean;
  onDeleteClose: () => void;
}

const FeesGroupModal: React.FC<FeesGroupModalProps> = ({ 
  feeGroupToEdit, 
  showEditModal, 
  onClose,
  showAddModal,
  onAddClose,
  showDeleteModal,
  onDeleteClose
}) => {
  const [editFormData, setEditFormData] = useState({
    name: '',
    description: '',
    status: false
  });
  const [addFormData, setAddFormData] = useState({
    name: '',
    description: '',
    status: false
  });

  useEffect(() => {
    if (feeGroupToEdit) {
      setEditFormData({
        name: feeGroupToEdit.name || '',
        description: feeGroupToEdit.description || '',
        status: feeGroupToEdit.status === "1"
      });
    }
  }, [feeGroupToEdit]);

  const handleInputChange = (form: 'edit' | 'add') => 
    (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
      const { name, value } = e.target;
      if (form === 'edit') {
        setEditFormData(prev => ({ ...prev, [name]: value }));
      } else {
        setAddFormData(prev => ({ ...prev, [name]: value }));
      }
    };

  const handleStatusChange = (form: 'edit' | 'add') => 
    (e: React.ChangeEvent<HTMLInputElement>) => {
      const checked = e.target.checked;
      if (form === 'edit') {
        setEditFormData(prev => ({ ...prev, status: checked }));
      } else {
        setAddFormData(prev => ({ ...prev, status: checked }));
      }
    };

  const handleUpdate = async () => {
    try {
      console.log('Updating:', editFormData);
      onClose();
    } catch (error) {
      console.error('Update failed:', error);
    }
  };

  const handleAdd = async () => {
    try {
      console.log('Adding:', addFormData);
      onAddClose();
      setAddFormData({ name: '', description: '', status: false });
    } catch (error) {
      console.error('Add failed:', error);
    }
  };

  const handleDelete = async () => {
    try {
      console.log('Deleting:', feeGroupToEdit?.id);
      onDeleteClose();
    } catch (error) {
      console.error('Delete failed:', error);
    }
  };

  return (
    <>
      {/* Edit Modal */}
      {showEditModal && (
        <div className="modal-backdrop fade show">
          <div className="modal d-block" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content">
                <div className="modal-header">
                  <h5 className="modal-title">Edit Fees Group</h5>
                  <button type="button" className="btn-close" onClick={onClose}></button>
                </div>
                <div className="modal-body">
                  {/* Edit form content */}
                </div>
                <div className="modal-footer">
                  <button type="button" className="btn btn-secondary" onClick={onClose}>
                    Cancel
                  </button>
                  <button type="button" className="btn btn-primary" onClick={handleUpdate}>
                    Save Changes
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Add Modal */}
      {showAddModal && (
        <div className="modal-backdrop fade show">
          <div className="modal d-block" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content">
                {/* Add modal content */}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Delete Modal */}
      {showDeleteModal && (
        <div className="modal-backdrop fade show">
          <div className="modal d-block" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content">
                <div className="modal-body text-center">
                  <span className="delete-icon">
                    <i className="ti ti-trash-x" />
                  </span>
                  <h4>Confirm Deletion</h4>
                  <p>Are you sure you want to delete this item?</p>
                  <div className="d-flex justify-content-center">
                    <button className="btn btn-light me-3" onClick={onDeleteClose}>
                      Cancel
                    </button>
                    <button className="btn btn-danger" onClick={handleDelete}>
                      Yes, Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default FeesGroupModal;