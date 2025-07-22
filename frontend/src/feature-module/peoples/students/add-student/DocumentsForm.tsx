import React from "react";

interface DocumentsFormProps {
  currentStep: number;
  setCurrentStep: React.Dispatch<React.SetStateAction<number>>;
  setFormData: React.Dispatch<React.SetStateAction<any>>;
  documents: {
    birthCertificate: File | null;
    aadharCard: File | null;
    transferCertificate: File | null;
  };
  setDocuments: React.Dispatch<
    React.SetStateAction<{
      birthCertificate: File | null;
      aadharCard: File | null;
      transferCertificate: File | null;
    }>
  >;
  isEdit: boolean;
}

const DocumentsForm: React.FC<DocumentsFormProps> = ({
  currentStep,
  setCurrentStep,
  setFormData,
  documents,
  setDocuments,
  isEdit,
}) => {
  const handleFileChange = (
    field: "birthCertificate" | "aadharCard" | "transferCertificate",
    e: React.ChangeEvent<HTMLInputElement>
  ) => {
    const file = e.target.files && e.target.files[0];
    if (file) {
      setDocuments((prev) => ({ ...prev, [field]: file }));
    }
  };

  React.useEffect(() => {
    if (currentStep === 5) {
      const payload = {
        birthCertificate: documents.birthCertificate,
        aadharCard: documents.aadharCard,
        transferCertificate: documents.transferCertificate,
      };
      console.log("✅ Step 5 Payload: Documents", payload);
      setFormData((prev: FormData) => ({ ...prev, documents: payload }));
    }
  }, [currentStep]);

  return (
    <div className="card">
      <div className="card-header bg-light">
        <div className="d-flex align-items-center">
          <span className="bg-white avatar avatar-sm me-2 text-gray-7 flex-shrink-0">
            <i className="ti ti-file fs-16" />
          </span>
          <h4 className="text-dark">Documents</h4>
        </div>
      </div>
      <div className="card-body pb-1">
        <div className="row">
          {/* Birth Certificate */}
          <div className="col-lg-6">
            <div className="mb-2">
              <div className="mb-3">
                <label className="form-label mb-1">Birth Certificate</label>
                <p>Upload image size of 4MB, Accepted Format PDF</p>
              </div>
              <div className="d-flex align-items-center flex-wrap">
                <div className="btn btn-primary drag-upload-btn mb-2 me-2">
                  <i className="ti ti-file-upload me-1" />
                  Upload Document
                  <input
                    type="file"
                    className="form-control image_sign"
                    accept="application/pdf"
                    onChange={(e) => handleFileChange("birthCertificate", e)}
                  />
                </div>
                {documents.birthCertificate && (
                  <p className="mb-2">{documents.birthCertificate.name}</p>
                )}
              </div>
            </div>
          </div>

          {/* Aadhar Card */}
          <div className="col-lg-6">
            <div className="mb-2">
              <div className="mb-3">
                <label className="form-label mb-1">Aadhar Card</label>
                <p>Upload image size of 4MB, Accepted Format PDF</p>
              </div>
              <div className="d-flex align-items-center flex-wrap">
                <div className="btn btn-primary drag-upload-btn mb-2 me-2">
                  <i className="ti ti-file-upload me-1" />
                  Upload Document
                  <input
                    type="file"
                    className="form-control image_sign"
                    accept="application/pdf"
                    onChange={(e) => handleFileChange("aadharCard", e)}
                  />
                </div>
                {documents.aadharCard && (
                  <p className="mb-2">{documents.aadharCard.name}</p>
                )}
              </div>
            </div>
          </div>

          {/* Transfer Certificate */}
          <div className="col-lg-6">
            <div className="mb-2">
              <div className="mb-3">
                <label className="form-label mb-1">Upload Transfer Certificate</label>
                <p>Upload image size of 4MB, Accepted Format PDF</p>
              </div>
              <div className="d-flex align-items-center flex-wrap">
                <div className="btn btn-primary drag-upload-btn mb-2 me-2">
                  <i className="ti ti-file-upload me-1" />
                  Upload Document
                  <input
                    type="file"
                    className="form-control image_sign"
                    accept="application/pdf"
                    onChange={(e) => handleFileChange("transferCertificate", e)}
                  />
                </div>
                {documents.transferCertificate && (
                  <p className="mb-2">{documents.transferCertificate.name}</p>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DocumentsForm;
