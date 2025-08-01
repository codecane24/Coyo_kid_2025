import React from "react";
import Select, { MultiValue, ActionMeta } from "react-select";

export type OptionType = {
  value: any;
  label: string;
};

export interface CommonSelectMultiProps<T = OptionType> {
  options: T[];
  defaultValue?: T[];
  value?: T[];
  className?: string;
  styles?: any;
  onChange?: (option: T[]) => void;
  placeholder?: string;
}

const CommonSelectMulti = <T extends OptionType>({
  options,
  defaultValue,
  value,
  className,
  onChange,
  placeholder = "Select",
}: CommonSelectMultiProps<T>) => {
  const handleChange = (
    newValue: MultiValue<T>,
    _actionMeta: ActionMeta<T>
  ) => {
    if (onChange) {
      onChange(Array.isArray(newValue) ? [...newValue] : []);
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
      isMulti={true}
      placeholder={placeholder}
    />
  );
};

export default CommonSelectMulti;
