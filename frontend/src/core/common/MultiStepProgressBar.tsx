import React from "react";
import "./MultiStepProgressBar.css";
import { motion } from "framer-motion";
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
<motion.div
  className="step-item"
  key={index}
  onClick={() => canClick && onStepClick(stepNumber)}
  style={{ cursor: canClick ? "pointer" : "default" }}
>
  {/* Step Circle */}
  <motion.div
    className={`step-circle ${isCompleted ? "completed" : isActive ? "active" : ""}`}
    animate={{
      scale: isCompleted ? 1.1 : isActive ? 1.08 : 1,
      backgroundColor: isCompleted
        ? "#006fdd"              // completed - darker primary
        : isActive
        ? "#E6F0FA"             // current step
        : "#dee2e6",            // initial (bg-light)
      color: isCompleted ? "#fff" : "#000",
    }}
    transition={{ duration: 0.3, ease: "easeInOut" }}
  >
    {isCompleted ? "✔" : stepNumber}
  </motion.div>

  {/* Label */}
  <div className="step-label">{step}</div>

  {/* Connecting Line */}
  {index < steps.length - 1 && (
    <motion.div
      className="step-line"
      initial={false}
      animate={{
        backgroundColor: index < currentStep - 1 ? "#006fdd" : "#dee2e6", // gray-200
        width: "100%",
      }}
      transition={{ duration: 0.3 }}
    />
  )}
</motion.div>


        );
      })}
    </div>
  );
};

export default MultiStepProgressBar;
