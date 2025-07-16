import React from "react";
import "./MultiStepProgressBar.css";

interface Props {
  currentStep: number;
  steps: string[];
}

const MultiStepProgressBar: React.FC<Props> = ({ currentStep, steps }) => {
  return (
    <div className="stepper-container">
      {steps.map((step, index) => {
        const isCompleted = index < currentStep;
        const isActive = index === currentStep;

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
