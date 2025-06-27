import React from "react";

import { Outlet } from "react-router-dom";

const AuthFeature = () => {
  return (
    <div className="account-page">
      <div className="main-wrapper">
        <Outlet />
      </div>
    </div>
  );
};

export default AuthFeature;
