import React from 'react'
import { useAuth } from '../../context/AuthContext';
import { useNavigate } from 'react-router-dom';
function Unauthorized() {
   const { logout } = useAuth();
const navigate = useNavigate();
  const handleLogout = () => {
  logout();
  navigate("/");
};
  return (
    <div>

unauthorised
         <button
    onClick={handleLogout}
    className="btn logout-btn w-100 d-flex align-items-center justify-content-start px-4 py-2 gap-2"
  >
    <i className="fas fa-sign-out-alt"></i>
    <span className="fw-semibold">Logout</span>
  </button>
    </div>
  )
}

export default Unauthorized