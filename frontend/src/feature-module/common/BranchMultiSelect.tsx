import React, { useEffect, useState } from "react";
import { getBranch } from "../../services/Branches";

type Branch = {
  id: number;
  name: string;
};

type Props = {
  defaultSelected?: number[]; // optional for initial selection
  selectedBranchIds: number[]; // controlled value from parent
  onChange: (selected: number[]) => void;
};

const BranchMultiSelect: React.FC<Props> = ({
  defaultSelected = [],
  selectedBranchIds,
  onChange,
}) => {
  const [branchOptions, setBranchOptions] = useState<Branch[]>([]);

  useEffect(() => {
    const fetchBranches = async () => {
      try {
        const res = await getBranch();
        const branches: Branch[] = res?.data || [];
        setBranchOptions(branches);
      } catch (err) {
        console.error("Error fetching branches:", err);
      }
    };

    fetchBranches();
  }, []);

  const handleCheckboxChange = (id: number) => {
    const updated = selectedBranchIds.includes(id)
      ? selectedBranchIds.filter((item) => item !== id)
      : [...selectedBranchIds, id];

    onChange(updated); // send to parent
  };

  return (
    <div className="col-xxl col-xl-3 col-md-6">
      <div className="mb-3">
        <label className="form-label">Select Branches</label>
        <div className="d-flex flex-wrap gap-2">
          {branchOptions.map((branch) => (
            <div key={branch.id} className="form-check me-3">
              <input
                type="checkbox"
                id={`branch-${branch.id}`}
                className="form-check-input"
                checked={selectedBranchIds.includes(branch.id)}
                onChange={() => handleCheckboxChange(branch.id)}
              />
              <label className="form-check-label" htmlFor={`branch-${branch.id}`}>
                {branch.name}
              </label>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default BranchMultiSelect;
