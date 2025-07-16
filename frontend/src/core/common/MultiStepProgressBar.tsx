import React from "react";
import "./MultiStepProgressBar.css";

interface Props {
  currentStep: number; // 1-based
  steps: string[];
}

const MultiStepProgressBar: React.FC<Props> = ({ currentStep, steps }) => {
  return (
    <div className="stepper-container">
      {steps.map((step, index) => {
        const isCompleted = index + 1 < currentStep;
        const isActive = index + 1 === currentStep;

        return (
          <div className="step-item" key={index}>
            <div className={`step-circle ${isCompleted ? "completed" : isActive ? "active" : ""}`}>
              {isCompleted ? "✔" : index + 1}
            </div>
            <div className="step-label">{step}</div>
            {index < steps.length - 1 && <div className="step-line" />}
          </div>
        );
      })}
    </div>
  );
};

export default MultiStepProgressBar;
