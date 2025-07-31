import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { toast } from "react-toastify";
import { 
  createFeesGroup, 
  updateFeesGroup, 
  deleteFeesGroup 
} from "../../../services/FeesGroupData";


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
  refreshData: () => void; // Add this prop to refresh the list after operations
}

const FeesGroupModal: React.FC<FeesGroupModalProps> = ({ 
  feeGroupToEdit, 
  showEditModal, 
  onClose,
  showAddModal,
  onAddClose,
  showDeleteModal,
  onDeleteClose,
  refreshData
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
  const [isLoading, setIsLoading] = useState(false);
  // Removed error state, use Toasts for errors

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
      // Error handled by Toasts
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
    if (!feeGroupToEdit?.id) return;
    
    try {
      setIsLoading(true);
      const response = await updateFeesGroup(feeGroupToEdit.id, {
        name: editFormData.name || feeGroupToEdit.name || '',
        description: editFormData.description || feeGroupToEdit.description || '',
        status: editFormData.status ? "1" : "0"
      });
      refreshData();
      onClose();
      toast.success('Data updated successfully');
    } catch (error) {
      console.error('Update.. failed:', error);
      toast.error('Failed to update fees group. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleAdd = async () => {
    try {
      setIsLoading(true);
      await createFeesGroup({
        name: addFormData.name,
        description: addFormData.description,
        status: addFormData.status ? "1" : "0"
      });
      refreshData();
      onAddClose();
      setAddFormData({ name: '', description: '', status: false });
    } catch (error) {
      console.error('Add failed:', error);
      toast.error('Failed to create fees group. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!feeGroupToEdit?.id) return;
    
    try {
      setIsLoading(true);
      await deleteFeesGroup(feeGroupToEdit.id);
      refreshData();
      onDeleteClose();
    } catch (error) {
      console.error('Delete failed:', error);
      toast.error('Failed to delete fees group. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      {/* Edit Modal */}
      {showEditModal && (
        <>
          <div className="modal-backdrop fade show"></div>
          <div className="modal d-block" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content">
                <div className="modal-header">
                  <h5 className="modal-title">Edit Fees Group</h5>
                  <button 
                    type="button" 
                    className="btn-close" 
                    onClick={onClose}
                    disabled={isLoading}
                  ></button>
                </div>
                <div className="modal-body">
                  {/* Error Toasts handled globally */}
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Fees Group</label>
                        <input 
                          type="text" 
                          className="form-control" 
                          name="name"
                          value={editFormData.name}
                          onChange={handleInputChange('edit')}
                          placeholder="Enter Fees Group"
                          disabled={isLoading}
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Description</label>
                        <textarea
                          className="form-control"
                          rows={4}
                          name="description"
                          value={editFormData.description}
                          onChange={handleInputChange('edit')}
                          placeholder="Add Description"
                          disabled={isLoading}
                        />
                      </div>
                      <div className="d-flex align-items-center justify-content-between">
                        <div className="status-title">
                          <h5>Status</h5>
                          <p>Change the Status by toggle</p>
                        </div>
                        <div className="form-check form-switch">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            role="switch"
                            checked={editFormData.status}
                            onChange={handleStatusChange('edit')}
                            disabled={isLoading}
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="modal-footer">
                  <button 
                    type="button" 
                    className="btn btn-light me-2" 
                    onClick={onClose}
                    disabled={isLoading}
                  >
                    Cancel
                  </button>
                  <button 
                    type="button" 
                    className="btn btn-primary" 
                    onClick={handleUpdate}
                    disabled={isLoading}
                  >
                    {isLoading ? 'Saving...' : 'Save Changes'}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </>
      )}

      {/* Add Modal */}
      {showAddModal && (
        <>
          <div className="modal-backdrop fade show"></div>
          <div className="modal d-block" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content">
                <div className="modal-header">
                  <h5 className="modal-title">Add Fees Group</h5>
                  <button 
                    type="button" 
                    className="btn-close" 
                    onClick={onAddClose}
                    disabled={isLoading}
                  ></button>
                </div>
                <div className="modal-body">
                  {/* Error Toasts handled globally */}
                  <div className="row">
                    <div className="col-md-12">
                      <div className="mb-3">
                        <label className="form-label">Fees Group</label>
                        <input 
                          type="text" 
                          className="form-control" 
                          name="name"
                          value={addFormData.name}
                          onChange={handleInputChange('add')}
                          placeholder="Enter Fees Group"
                          disabled={isLoading}
                        />
                      </div>
                      <div className="mb-3">
                        <label className="form-label">Description</label>
                        <textarea
                          className="form-control"
                          rows={4}
                          name="description"
                          value={addFormData.description}
                          onChange={handleInputChange('add')}
                          placeholder="Add Description"
                          disabled={isLoading}
                        />
                      </div>
                      <div className="d-flex align-items-center justify-content-between">
                        <div className="status-title">
                          <h5>Status</h5>
                          <p>Change the Status by toggle</p>
                        </div>
                        <div className="form-check form-switch">
                          <input
                            className="form-check-input"
                            type="checkbox"
                            role="switch"
                            checked={addFormData.status}
                            onChange={handleStatusChange('add')}
                            disabled={isLoading}
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="modal-footer">
                  <button 
                    type="button" 
                    className="btn btn-light me-2" 
                    onClick={onAddClose}
                    disabled={isLoading}
                  >
                    Cancel
                  </button>
                  <button 
                    type="button" 
                    className="btn btn-primary" 
                    onClick={handleAdd}
                    disabled={isLoading}
                  >
                    {isLoading ? 'Adding...' : 'Add Fees Group'}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </>
      )}

      {/* Delete Modal */}
      {showDeleteModal && (
        <>
          <div className="modal-backdrop fade show"></div>
          <div className="modal d-block" tabIndex={-1}>
            <div className="modal-dialog modal-dialog-centered">
              <div className="modal-content">
                <div className="modal-body text-center">
                  {/* Error Toasts handled globally */}
                  <span className="delete-icon">
                    <i className="ti ti-trash-x" />
                  </span>
                  <h4>Confirm Deletion</h4>
                  <p>Are you sure you want to delete this item?</p>
                  <div className="d-flex justify-content-center">
                    <button 
                      className="btn btn-light me-3" 
                      onClick={onDeleteClose}
                      disabled={isLoading}
                    >
                      Cancel
                    </button>
                    <button 
                      className="btn btn-danger" 
                      onClick={handleDelete}
                      disabled={isLoading}
                    >
                      {isLoading ? 'Deleting...' : 'Yes, Delete'}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </>
  );
};

export default FeesGroupModal;