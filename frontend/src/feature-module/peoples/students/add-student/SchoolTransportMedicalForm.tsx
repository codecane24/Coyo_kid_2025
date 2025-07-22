import React from "react";

export type TransportMedicalFormData = {
  transportService: string;
  seriousDisease: string;
  seriousInjuries: string[];
  allergies: string[];
  medications: string[];
  previousSchoolName: string;
  previousSchoolAddress: string;
};

type Props = {
  currentStep: number;
  setFormData: (prev: (data: any) => any) => void;
  transportMedical: TransportMedicalFormData;
  setTransportMedical: React.Dispatch<React.SetStateAction<TransportMedicalFormData>>;
};

const SchoolTransportMedicalForm = ({
  currentStep,
  setFormData,
  transportMedical,
  setTransportMedical,
}: Props) => {
  // Sync full formData on each change to transportMedical
  React.useEffect(() => {
    setFormData((prev: any) => ({
      ...prev,
      transportMedical,
    }));
  }, [transportMedical]);

  const handleChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    const { name, value } = e.target;
    setTransportMedical((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleArrayChange = (
    e: React.ChangeEvent<HTMLInputElement>,
    key: keyof TransportMedicalFormData
  ) => {
    const value = e.target.value;
    setTransportMedical((prev) => ({
      ...prev,
      [key]: value.split(",").map((item) => item.trim()).filter(Boolean),
    }));
  };

  if (currentStep !== 4) return null;

return (
  <div className="card">
    <div className="card-header bg-light">
      <div className="d-flex align-items-center">
        <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
          <i className="ti ti-ambulance fs-16" />
        </span>
        <h4 className="text-dark">Transport & Medical Details</h4>
      </div>
    </div>

    <div className="card-body pb-1">
      <div className="row mb-3">
        <label className="form-label">Transport Service:</label>
        <div className="d-flex align-items-center">
          <div className="form-check me-3">
            <input
              className="form-check-input"
              type="radio"
              name="transportService"
              value="yes"
              checked={transportMedical.transportService === "yes"}
              onChange={handleChange}
            />
            <label className="form-check-label">Yes</label>
          </div>
          <div className="form-check">
            <input
              className="form-check-input"
              type="radio"
              name="transportService"
              value="no"
              checked={transportMedical.transportService === "no"}
              onChange={handleChange}
            />
            <label className="form-check-label">No</label>
          </div>
        </div>
      </div>

      <div className="row mb-3">
        <label className="form-label">Serious Disease:</label>
        <input
          type="text"
          name="seriousDisease"
          className="form-control"
          value={transportMedical.seriousDisease}
          onChange={handleChange}
        />
      </div>

      <div className="row mb-3">
        <label className="form-label">Serious Injuries (comma separated):</label>
        <input
          type="text"
          className="form-control"
          value={transportMedical.seriousInjuries.join(", ")}
          onChange={(e) => handleArrayChange(e, "seriousInjuries")}
        />
      </div>

      <div className="row mb-3">
        <label className="form-label">Allergies (comma separated):</label>
        <input
          type="text"
          className="form-control"
          value={transportMedical.allergies.join(", ")}
          onChange={(e) => handleArrayChange(e, "allergies")}
        />
      </div>

      <div className="row mb-3">
        <label className="form-label">Medications (comma separated):</label>
        <input
          type="text"
          className="form-control"
          value={transportMedical.medications.join(", ")}
          onChange={(e) => handleArrayChange(e, "medications")}
        />
      </div>

      <div className="row mb-3">
        <label className="form-label">Previous School Name:</label>
        <input
          type="text"
          name="previousSchoolName"
          className="form-control"
          value={transportMedical.previousSchoolName}
          onChange={handleChange}
        />
      </div>

      <div className="row mb-3">
        <label className="form-label">Previous School Address:</label>
        <textarea
          name="previousSchoolAddress"
          className="form-control"
          rows={3}
          value={transportMedical.previousSchoolAddress}
          onChange={handleChange}
        />
      </div>
    </div>
  </div>
);

};

export default SchoolTransportMedicalForm;
