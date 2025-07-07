import React from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles?: string[];
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children, allowedRoles }) => {
  const { user, token } = useAuth();
  console.log(user,token)
console.log("ProtectedRoute user:", user);
console.log("ProtectedRoute token:", token);
  if (!token || !user) {
    return <Navigate to="/" replace />; // redirect to login
  }

  const role = user.role || user.type || "";

  if (allowedRoles && !allowedRoles.includes(role)) {
    return <Navigate to="/unauthorized" replace />;
  }

  return <>{children}</>;
};

export default ProtectedRoute;
