// src/components/Loader.tsx
import React from "react";
import './loader.css'; // We'll define animation here

const LoaderTwo: React.FC = () => {
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

export default LoaderTwo;
