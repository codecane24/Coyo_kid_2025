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

  // ✅ Allow login page if user has multiple branches but hasn't selected one yet
  const hasMultipleBranches = Array.isArray(branches) && branches.length > 1;

  if (token && user) {
    if (!selectedBranch && hasMultipleBranches) {
      // Allow login page to show dropdown
      return <>{children}</>;
    }

    // 🚀 Redirect to dashboard if branch is selected and user is valid
    const role = user.role || user.type || "";
    switch (role) {
      case "admin":
        return <Navigate to="/index" replace />;
      case "teacher":
        return <Navigate to="/teacher-dashboard" replace />;
      case "student":
      case "parent":
        return <Navigate to="/student-dashboard" replace />;
      default:
        return <Navigate to="/unauthorized" replace />;
    }
  }

  return <>{children}</>;
};

export default PublicRoute;
