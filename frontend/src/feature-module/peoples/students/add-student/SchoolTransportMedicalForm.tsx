import React, { useEffect, useState } from "react";
import { TagsInput } from "react-tag-input-component";

interface TransportMedicalFormData {
  transportService: string;
  seriousDisease: string;
  seriousInjuries: string[];
  allergies: string[];
  medications: string[];
  previousSchoolName: string;
  previousSchoolAddress: string;
}

interface Props {
  currentStep: number;
  setCurrentStep: React.Dispatch<React.SetStateAction<number>>;
  setFormData: React.Dispatch<React.SetStateAction<any>>;
  isEdit?: boolean;
  formData: any;
}

const SchoolTransportMedicalForm: React.FC<Props> = ({
  currentStep,
  setCurrentStep,
  setFormData,
  isEdit = false,
  formData,
}) => {
  const [form, setForm] = useState<TransportMedicalFormData>({
    transportService: "",
    seriousDisease: "",
    seriousInjuries: [],
    allergies: [],
    medications: [],
    previousSchoolName: "",
    previousSchoolAddress: "",
  });

  const [errors, setErrors] = useState<{ [key: string]: boolean }>({});

  useEffect(() => {
    // prefill from parent data if editing
    if (isEdit && formData?.transportMedical) {
      setForm(formData.transportMedical);
    }
  }, [isEdit, formData]);

  const handleInput = (field: keyof TransportMedicalFormData, value: any) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  useEffect(() => {
    if (currentStep === 4) {
      const required = [
        "transportService",
        "seriousDisease",
        "previousSchoolName",
        "previousSchoolAddress",
      ];
      const newErrors: { [key: string]: boolean } = {};
      required.forEach((field) => {
        if (!form[field as keyof TransportMedicalFormData]) {
          newErrors[field] = true;
        }
      });

      setErrors(newErrors);

      if (Object.keys(newErrors).length === 0) {
        console.log("✅ Step 4 Payload:", form);
        setFormData((prev: any) => ({
          ...prev,
          transportMedical: form,
        }));
      }
    }
  }, [currentStep]);

  return (
    <>
      {/* Transport Information */}
      <div className="card">
        <div className="card-header bg-light d-flex align-items-center">
          <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
            <i className="ti ti-bus-stop fs-16" />
          </span>
          <h4 className="text-dark">Transport Information</h4>
        </div>

        <div className="mb-3 pl-3">
          <label className="form-label">Avail Transport Service</label>
          <div>
            {["yes", "no"].map((val) => (
              <div className="form-check form-check-inline" key={val}>
                <input
                  className="form-check-input"
                  type="radio"
                  name="transportService"
                  id={`transport-${val}`}
                  value={val}
                  checked={form.transportService === val}
                  onChange={(e) => handleInput("transportService", e.target.value)}
                />
                <label className="form-check-label" htmlFor={`transport-${val}`}>
                  {val === "yes" ? "Yes" : "No"}
                </label>
              </div>
            ))}
          </div>
          {errors.transportService && <small className="text-danger">This is required</small>}
        </div>
      </div>

      {/* Medical History */}
      <div className="card">
        <div className="card-header bg-light">
          <div className="d-flex align-items-center">
            <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
              <i className="ti ti-medical-cross fs-16" />
            </span>
            <h4 className="text-dark">Medical History</h4>
          </div>
        </div>
        <div className="card-body pb-1">
          <div className="mb-3">
            <label className="form-label">Any serious disease in past?</label>
            <div className="d-flex">
              {["yes", "no"].map((val) => (
                <div className="form-check me-3 mb-2" key={val}>
                  <input
                    className="form-check-input"
                    type="radio"
                    name="seriousDisease"
                    id={`disease-${val}`}
                    value={val}
                    checked={form.seriousDisease === val}
                    onChange={(e) => handleInput("seriousDisease", e.target.value)}
                  />
                  <label className="form-check-label" htmlFor={`disease-${val}`}>
                    {val === "yes" ? "Yes" : "No"}
                  </label>
                </div>
              ))}
            </div>
            {errors.seriousDisease && <small className="text-danger">This is required</small>}
          </div>

          <div className="mb-3">
            <label className="form-label">Any Serious Injury In Past?</label>
            <TagsInput value={form.seriousInjuries} onChange={(val) => handleInput("seriousInjuries", val)} />
          </div>

          <div className="mb-3">
            <label className="form-label">Allergies</label>
            <TagsInput value={form.allergies} onChange={(val) => handleInput("allergies", val)} />
          </div>

          <div className="mb-3">
            <label className="form-label">Medications</label>
            <TagsInput value={form.medications} onChange={(val) => handleInput("medications", val)} />
          </div>
        </div>
      </div>

      {/* Previous School Details */}
      <div className="card">
        <div className="card-header bg-light">
          <div className="d-flex align-items-center">
            <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
              <i className="ti ti-building fs-16" />
            </span>
            <h4 className="text-dark">Previous School Details</h4>
          </div>
        </div>
        <div className="card-body pb-1">
          <div className="row">
            <div className="col-md-6">
              <div className="mb-3">
                <label className="form-label">School Name</label>
                <input
                  type="text"
                  className={`form-control ${errors.previousSchoolName ? "border border-danger" : ""}`}
                  value={form.previousSchoolName}
                  onChange={(e) => handleInput("previousSchoolName", e.target.value)}
                />
              </div>
            </div>
            <div className="col-md-6">
              <div className="mb-3">
                <label className="form-label">Address</label>
                <input
                  type="text"
                  className={`form-control ${errors.previousSchoolAddress ? "border border-danger" : ""}`}
                  value={form.previousSchoolAddress}
                  onChange={(e) => handleInput("previousSchoolAddress", e.target.value)}
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default SchoolTransportMedicalForm;
