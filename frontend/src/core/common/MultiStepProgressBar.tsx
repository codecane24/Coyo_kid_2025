import React from "react";
import "./MultiStepProgressBar.css";

interface Props {
  currentStep: number;
  steps: string[];
  onStepClick?: (stepNumber: number) => void;
  isEditMode?: boolean; // ✅ Add this
  studentId?: string;
}

const MultiStepProgressBar: React.FC<Props> = ({
  currentStep,
  steps,
  onStepClick,
  isEditMode = false,
  studentId,
}) => {
  return (
    <div className="stepper-container">
      {steps.map((step, index) => {
        const stepNumber = index + 1;
        const isCompleted = stepNumber < currentStep;
        const isActive = stepNumber === currentStep;

        const canClick = isEditMode && typeof onStepClick === "function"; // ✅ Use isEditMode

        return (
          <div
            className="step-item"
            key={index}
            onClick={() => canClick && onStepClick(stepNumber)}
            style={{ cursor: canClick ? "pointer" : "default" }}
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
