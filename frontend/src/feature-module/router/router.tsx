import React from "react";
import { Route, Routes } from "react-router-dom";
import { authRoutes, publicRoutes } from "./router.link";
import Feature from "../feature";
import AuthFeature from "../authFeature";
import Login from "../auth/login/login";
import ProtectedRoute from "../../routes/ProtectedRoute";

const ALLRoutes: React.FC = () => {
  return (
    <Routes>
      {/* Public Routes */}
      <Route path="/" element={<Login />} />
      <Route path="/unauthorized" element={<div>Un Authorized</div>} />

      {/* Public Layout Routes */}
      <Route element={<Feature />}>
        {publicRoutes.map((route, idx) => (
          <Route path={route.path} element={route.element} key={idx} />
        ))}
      </Route>

      {/* Protected + Role-Based Routes */}
      <Route
        element={
          <ProtectedRoute allowedRoles={["admin", "teacher", "student"]}>
            <AuthFeature />
          </ProtectedRoute>
        }
      >
        {authRoutes.map((route, idx) => (
          <Route path={route.path} element={route.element} key={idx} />
        ))}
      </Route>
    </Routes>
  );
};

export default ALLRoutes;
