import React from "react";
import "./MultiStepProgressBar.css";

interface Props {
  currentStep: number; // 1-based
  steps: string[];
  onStepClick?: (stepNumber: number) => void; // ✅ optional prop for click
}

const MultiStepProgressBar: React.FC<Props> = ({ currentStep, steps, onStepClick }) => {
  return (
    <div className="stepper-container">
      {steps.map((step, index) => {
        const isCompleted = index + 1 < currentStep;
        const isActive = index + 1 === currentStep;

        return (
          <div
            className="step-item"
            key={index}
            onClick={() => onStepClick?.(index + 1)} // ✅ only if provided
            style={{ cursor: onStepClick ? "pointer" : "default" }} // ✅ UX improvement
          >
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
