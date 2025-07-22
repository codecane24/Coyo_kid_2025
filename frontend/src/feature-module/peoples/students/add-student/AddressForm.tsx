import React, { useState } from "react";

type AddressFields = "address" | "area" | "landmark" | "city" | "state" | "pincode";
type AddressType = {
  [key in AddressFields]: string;
};

interface Props {
  currentStep: number;
  setCurrentStep: React.Dispatch<React.SetStateAction<number>>;
  setFormData: React.Dispatch<React.SetStateAction<any>>;
  addressInfo: {
    permanent: AddressType;
    current: AddressType;
  };
  setAddressInfo: React.Dispatch<React.SetStateAction<{
    permanent: AddressType;
    current: AddressType;
  }>>;
  isEdit: boolean;
}

const AddressForm: React.FC<Props> = ({
  currentStep,
  setCurrentStep,
  setFormData,
  addressInfo,
  setAddressInfo,
  isEdit,
}) => {
  const [errors, setErrors] = useState<{ [key: string]: boolean }>({});

  const validateFields = () => {
    const requiredFields = [
      "permanent.address",
      "permanent.area",
      "permanent.landmark",
      "permanent.city",
      "permanent.state",
      "permanent.pincode",
      "current.address",
      "current.area",
      "current.landmark",
      "current.city",
      "current.state",
      "current.pincode",
    ];

    const newErrors: { [key: string]: boolean } = {};
    requiredFields.forEach((field) => {
      const [section, key] = field.split(".") as ["permanent" | "current", AddressFields];
      if (!addressInfo[section][key]) {
        newErrors[field] = true;
      }
    });

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = () => {
    if (!validateFields()) return;

    const payload = {
      permanentAddress: addressInfo.permanent,
      currentAddress: addressInfo.current,
    };

    console.log("Payload: Address Info", payload);

    setFormData((prev: any) => ({
      ...prev,
      addressInfo: payload,
    }));

    setCurrentStep(currentStep + 1);
  };

  const handleChange = (section: "permanent" | "current", field: AddressFields, value: string) => {
    setAddressInfo((prev) => ({
      ...prev,
      [section]: {
        ...prev[section],
        [field]: value,
      },
    }));
  };

  const renderInput = (
    section: "permanent" | "current",
    label: string,
    field: AddressFields
  ) => (
    <div className="col-md-6" key={`${section}-${field}`}>
      <div className="mb-3">
        <label className="form-label">{label}</label>
        <input
          type="text"
          className={`form-control ${errors[`${section}.${field}`] ? "border border-danger" : ""}`}
          value={addressInfo[section][field]}
          onChange={(e) => handleChange(section, field, e.target.value)}
        />
      </div>
    </div>
  );

  const addressFields: { field: AddressFields; label: string }[] = [
    { field: "address", label: "House No. & Colony Name" },
    { field: "area", label: "Area" },
    { field: "landmark", label: "Landmark" },
    { field: "city", label: "City" },
    { field: "state", label: "State" },
    { field: "pincode", label: "Pincode" },
  ];

  return (
    <>
      {/* Current Address */}
      <div className="card">
        <div className="card-header bg-light">
          <div className="d-flex align-items-center">
            <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
              <i className="ti ti-map fs-16" />
            </span>
            <h4 className="text-dark">Current Address</h4>
          </div>
        </div>
        <div className="card-body pb-1">
          <div className="row">
            {addressFields.map((f) => renderInput("current", f.label, f.field))}
          </div>
        </div>
      </div>
      {/* Permanent Address */}
      <div className="card">
        <div className="card-header bg-light">
          <div className="d-flex align-items-center">
            <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
              <i className="ti ti-map fs-16" />
            </span>
            <h4 className="text-dark">Permanent Address</h4>
          </div>
        </div>
        <div className="card-body pb-1">
          <div className="row">
            {addressFields.map((f) => renderInput("permanent", f.label, f.field))}
          </div>
        </div>
      </div>

    
    </>
  );
};

export default AddressForm;
