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
 <div className="d-flex justify-content-center align-items-center vh-100 bg-white">
      <div className="loader-container position-relative">
        {/* Orbiting dots */}
        <div className="dot dot1 bg-green"></div>
        <div className="dot dot2 bg-blue"></div>
        <div className="dot dot3 bg-yellow"></div>
        <div className="dot dot4 bg-pink"></div>

        {/* Central logo */}
        <img src='assets/img/main-logo.png' alt="logo" className="loader-logo" />
      </div>
    </div>
  );
};

export default Loader;
