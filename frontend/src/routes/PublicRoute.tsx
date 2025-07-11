import React from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

interface PublicRouteProps {
  children: React.ReactNode;
}

const PublicRoute: React.FC<PublicRouteProps> = ({ children }) => {
  const { user, token } = useAuth();

  const selectedBranch = localStorage.getItem("selectedBranch");
  const branches = user?.branches || [];

  const role = user?.role || user?.type || "";
  console.log(role)
  const hasMultipleBranches = Array.isArray(branches) && branches.length > 1;

  // ✅ If user is logged in
  if (token && user) {
    // ✅ Super admin: skip branch logic, redirect directly
    
    if (role === "superadmin") {
      return <Navigate to="/index" replace />;
    }

    // ✅ Other roles: check if branch is selected (only when multiple exist)
    if (!selectedBranch && hasMultipleBranches) {
      return <>{children}</>; // allow branch dropdown
    }

    // ✅ Redirect other roles based on role
    switch (role) {
      case "admin":
        return <Navigate to="/index" replace />;
      case "teacher":
        return <Navigate to="/teacher-dashboard" replace />;
      case "student":
      case "parent":
        return <Navigate to="/student-dashboard" replace />;
      default:
        return <Navigate to="/" replace />;
    }
  }

  // Not logged in → allow public route
  return <>{children}</>;
};

export default PublicRoute;
