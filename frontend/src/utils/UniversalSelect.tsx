// src/components/Common/UniversalSelect.tsx
import React, { useEffect, useState } from "react";
import { getClassesList } from "../services/ClassData";
import { toast } from "react-toastify";

type OptionType = {
  label: string;
  value: string | number;
};

interface UniversalSelectProps {
  value: string | number;
  onChange: (value: string | number) => void;
  label?: string;
  error?: boolean;
  required?: boolean;
  className?: string;
  options?: OptionType[]; // For non-class selects
  fieldKey?: string; // Detect if it's "class"
}

const UniversalSelect: React.FC<UniversalSelectProps> = ({
  value,
  onChange,
  label = "Select Option",
  error = false,
  required = false,
  className = "",
  options = [],
  fieldKey = ""
}) => {
  const [internalOptions, setInternalOptions] = useState<OptionType[]>([]);

  useEffect(() => {
    async function fetchData() {
      try {
        if (fieldKey === "class") {
          const classes = await getClassesList();
          const formatted = Array.isArray(classes)
            ? classes.map((cls: any) => ({
                label: `${cls.name} (${cls.section})`,
                value: String(cls.id) // ensure string for matching
              }))
            : [];
          setInternalOptions(formatted);
        } else {
          // For generic selects, just use provided options
          setInternalOptions(
            options.map(opt => ({
              ...opt,
              value: String(opt.value) // ensure string
            }))
          );
        }
      } catch (error) {
        toast.error("Failed to load options");
      }
    }
    fetchData();
  }, [fieldKey, options]);

  return (
    <div className="mb-3">
      {label && (
        <label className="form-label">
          {label} {required && <span className="text-danger ms-1">*</span>}
        </label>
      )}
      <select
        name={fieldKey}
        className={`form-select ${error ? "is-invalid" : ""} ${className}`}
        value={String(value) || ""}
        onChange={(e) => onChange(e.target.value)}
      >
        <option value="">-- Select --</option>
        {internalOptions.map((option) => (
          <option key={option.value} value={String(option.value)}>
            {option.label}
          </option>
        ))}
      </select>
    </div>
  );
};

export default UniversalSelect;
