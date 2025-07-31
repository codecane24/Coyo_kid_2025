import React from "react";
import "./MultiStepProgressBar.css";

interface Props {
  currentStep: number; // 1-based
  steps: string[];
  onStepClick?: (stepNumber: number) => void;
  studentId?: string; // ✅ NEW — to determine edit mode
}

const MultiStepProgressBar: React.FC<Props> = ({ currentStep, steps, onStepClick, studentId }) => {
  return (
    <div className="stepper-container">
      {steps.map((step, index) => {
        const stepNumber = index + 1;
        const isCompleted = stepNumber < currentStep;
        const isActive = stepNumber === currentStep;

        const canClick = !!studentId && typeof onStepClick === "function";

        return (
          <div
            className="step-item"
            key={index}
            onClick={() => canClick && onStepClick(stepNumber)} // ✅ only allow click in edit mode
            style={{ cursor: canClick ? "pointer" : "default" }} // ✅ pointer only if clickable
          >
            <div className={`step-circle ${isCompleted ? "completed" : isActive ? "active" : ""}`}>
              {isCompleted ? "✔" : stepNumber}
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
