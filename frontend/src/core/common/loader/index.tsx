import React, { useState, useEffect } from "react";
import { useLocation } from "react-router-dom"; // Assuming you're using React Router
import { all_routes } from "../../../feature-module/router/all_routes";

const Loader = () => {
  const routes = all_routes
  const location = useLocation();
  const [showLoader, setShowLoader] = useState(false);

  useEffect(() => {
      

    if (location.pathname === routes.adminDashboard || location.pathname === routes.teacherDashboard 
      || location.pathname === routes.studentDashboard || location.pathname === routes.parentDashboard 
    ) {
      
      // Show the loader when navigating to a new route
      setShowLoader(true);

      // Hide the loader after 2 seconds
      const timeoutId = setTimeout(() => {
        setShowLoader(false);
      }, 2000);

      return () => {
        clearTimeout(timeoutId); // Clear the timeout when component unmounts
      };
    }else {
      setShowLoader(false)
    }
  }, [location.pathname]);

  return (
    <>
      {showLoader && <Preloader />}
      <div>
        {/* Your other content goes here */}
      </div>
    </>
  );
};

const Preloader = () => {
  return (
    <div className="loader-container">
      <div className="pulse-wrapper">
        <img src="/assets/img/main-logo.png" alt="Logo" className="pulse-logo" />
        <div className="pulse-circle green"></div>
        <div className="pulse-circle blue"></div>
        <div className="pulse-circle yellow"></div>
        <div className="pulse-circle pink"></div>
        <div className="pulse-circle gray"></div>
      </div>
    </div>
  );
};

export default Loader;
