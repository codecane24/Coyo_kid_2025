import React from "react";
import Select from "react-select";

// Make it generic and fallback to default Option type
export type OptionType = {
  value: any;
  label: string;
};

export interface SelectProps<T = OptionType> {
  options: T[];
  defaultValue?: T;
  value?: T;
  className?: string;
  styles?: any;
  onChange?: (option: T) => void;
}

const CommonSelect = <T extends OptionType>({
  options,
  defaultValue,
  value,
  className,
  onChange,
}: SelectProps<T>) => {
  const handleChange = (option: T | null) => {
    if (option && onChange) {
      onChange(option);
    }
  };

  return (
    <Select
      classNamePrefix="react-select"
      className={className}
      options={options}
      value={value}
      onChange={handleChange}
      defaultValue={defaultValue}
      placeholder="Select"
    />
  );
};

export default CommonSelect;
