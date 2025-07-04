import React, { useEffect, useState } from "react";
import Select from "react-select";

export type Option = {
  value: string;
  label: string;
};

export interface SelectProps {
  options: Option[];
  defaultValue?: Option;
  className?: string;
  styles?: any;
  onChange?: (option: Option) => void; // ✅ added this
}

const CommonSelect: React.FC<SelectProps> = ({
  options,
  defaultValue,
  className,
  onChange,
}) => {
  const [selectedOption, setSelectedOption] = useState<Option | undefined>(defaultValue);

  const handleChange = (option: Option | null) => {
    setSelectedOption(option || undefined);
    if (option && onChange) {
      onChange(option); // ✅ call parent onChange
    }
  };

  useEffect(() => {
    setSelectedOption(defaultValue || undefined);
  }, [defaultValue]);

  return (
    <Select
      classNamePrefix="react-select"
      className={className}
      options={options}
      value={selectedOption}
      onChange={handleChange}
      placeholder="Select"
    />
  );
};

export default CommonSelect;
