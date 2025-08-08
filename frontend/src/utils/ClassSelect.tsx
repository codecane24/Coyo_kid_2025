import React, { useEffect, useState } from "react";
import { getClassesList } from "../services/ClassData";
import { toast } from "react-toastify";

type OptionType = {
  label: string;
  value: string;
};

interface ClassSelectProps {
  value: string | number;
  onChange: (value: string) => void;
  label?: string;
  error?: boolean;
  required?: boolean;
  className?: string;
  options?: OptionType[];  // optional external options
  fieldKey?: string;       // to identify 'class'
}

const ClassSelect: React.FC<ClassSelectProps> = ({
  value,
  onChange,
  label = "Select Class",
  error = false,
  required = false,
  className = "",
  options,
  fieldKey,
}) => {
  const [classOptions, setClassOptions] = useState<OptionType[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    async function fetchClasses() {
      if (fieldKey === "class" || !options) {
        setLoading(true);
        try {
          const classes = await getClassesList();
          const formatted = Array.isArray(classes)
            ? classes.map((cls: any) => ({
                label: `${cls.name} (${cls.section})`,
                value: String(cls.id),
              }))
            : [];
          setClassOptions(formatted);
        } catch (error) {
          toast.error("Failed to load classes");
        } finally {
          setLoading(false);
        }
      } else {
        setClassOptions(
          options.map((opt) => ({
            label: opt.label,
            value: String(opt.value),
          }))
        );
      }
    }

    fetchClasses();
  }, [fieldKey, options]);

  if (loading) {
    return (
      <div className="mb-3">
        {label && (
          <label className="form-label">
            {label} {required && <span className="text-danger ms-1">*</span>}
          </label>
        )}
        <div>Loading classes...</div>
      </div>
    );
  }

  return (
    <div className="mb-3">
      {label && (
   <></>
      )}
      <select
        name={fieldKey || "select"}
        className={`form-select ${error ? "is-invalid" : ""} ${className}`}
        value={String(value) || ""}
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
