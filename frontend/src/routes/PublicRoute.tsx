// src/routes/PublicRoute.tsx
import React from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

interface PublicRouteProps {
  children: React.ReactNode;
}

const PublicRoute: React.FC<PublicRouteProps> = ({ children }) => {
  const { user, token } = useAuth();

  if (token && user) {
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
