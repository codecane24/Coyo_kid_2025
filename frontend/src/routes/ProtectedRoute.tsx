import React from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useNavigate } from "react-router-dom";
interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles?: string[];
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children, allowedRoles }) => {
     const { logout } = useAuth();
const navigate = useNavigate();
  const handleLogout = () => {
  logout();
  navigate("/");
};
  const { user, token } = useAuth();
  console.log(user,token)
console.log("ProtectedRoute user:", user);
console.log("ProtectedRoute token:", token);
  if (!token || !user) {
    return <Navigate to="/" replace />; // redirect to login
  }

  const role = user.role || user.type || "";

  if (allowedRoles && !allowedRoles.includes(role)) {
    // custom logout function
 handleLogout()
  alert("You are unauthorized because role not defined / role not exist");
 
  }

  return <>{children}</>;
};

export default ProtectedRoute;
