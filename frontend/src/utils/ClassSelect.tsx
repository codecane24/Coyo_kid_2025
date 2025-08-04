// src/components/Common/ClassSelect.tsx
import React, { useEffect, useState } from "react";
import { getClassesList } from "../services/ClassData";
import { toast } from "react-toastify";

type OptionType = {
  label: string;
  value: string | number;
};

interface ClassSelectProps {
  value: string | number;
  onChange: (value: string | number) => void;
  label?: string;
  error?: boolean;
  required?: boolean;
  className?: string;
}

const ClassSelect: React.FC<ClassSelectProps> = ({
  value,
  onChange,
  label = "Select Class",
  error = false,
  required = false,
  className = ""
}) => {
  const [classOptions, setClassOptions] = useState<OptionType[]>([]);

  useEffect(() => {
    async function fetchClasses() {
      try {
        const classes = await getClassesList();
        const formatted = Array.isArray(classes)
          ? classes.map((cls: any) => ({
              label: `${cls.name} (${cls.section})`,
              value: cls.id
            }))
          : [];
        setClassOptions(formatted);
      } catch (error) {
        toast.error("Failed to load classes");
      }
    }
    fetchClasses();
  }, []);

  return (
    <div className="mb-3">
      {label && (
        <label className="form-label">
          {label} {required && <span className="text-danger ms-1">*</span>}
        </label>
      )}
      <select
        className={`form-select ${error ? "is-invalid" : ""} ${className}`}
        value={value}
        onChange={(e) => onChange(e.target.value)}
      >
        <option value="">-- Select --</option>
        {classOptions.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
    </div>
  );
};

export default ClassSelect;
