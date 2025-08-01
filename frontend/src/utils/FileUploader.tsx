import React, { useEffect, useState } from "react";
import Link from "antd/es/typography/Link";
import { buildImageUrl } from "./buildImageUrl";

interface FileUploaderProps {
  fileTypes?: string;
  previewType?: "image" | "both";
  onFileChange: (file: File | null) => void;
  file: File | null;
  imageUrl?: string; // ✅ for existing uploaded image path
}

const FileUploader: React.FC<FileUploaderProps> = ({
  fileTypes = "image/*",
  previewType = "image",
  onFileChange,
  file,
  imageUrl,
}) => {
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);

  // ✅ Update preview when file or imageUrl changes
  useEffect(() => {
    if (file) {
      const objectUrl = URL.createObjectURL(file);
      setPreviewUrl(objectUrl);

      return () => {
        URL.revokeObjectURL(objectUrl); // clean up
      };
    } else if (imageUrl) {
      setPreviewUrl(buildImageUrl(imageUrl));
    } else {
      setPreviewUrl(null);
    }
  }, [file, imageUrl]);

  const isImage = file?.type?.startsWith("image/");

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selected = e.target.files?.[0] || null;
    onFileChange(selected);
  };

  const handleRemove = () => {
    onFileChange(null);
  };

  return (
    <div className="d-flex align-items-center">
      {/* Preview Box */}
      <div
        className="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames"
        style={{ borderRadius: "8px", width: "80px", height: "80px" }}
      >
        {previewUrl ? (
          <img
            src={previewUrl}
            alt="preview"
            style={{
              width: "100%",
              height: "100%",
              objectFit: "cover",
              borderRadius: "8px",
            }}
          />
        ) : (
          <i className="ti ti-photo-plus fs-16" />
        )}
      </div>

      {/* Upload + Remove */}
      <div className="profile-upload">
        <div className="profile-uploader d-flex align-items-center">
          <div className="drag-upload-btn mb-2">
            Upload
            <input
              type="file"
              className="form-control image-sign"
              accept={fileTypes}
              onChange={handleChange}
            />
          </div>

          <Link className="btn btn-primary mb-2 ms-2" onClick={handleRemove}>
            Remove
          </Link>
        </div>

        {file && previewType === "both" && !isImage && (
          <p className="text-muted fs-12 mt-1">{file.name}</p>
        )}

        <p className="fs-12 text-sm text-gray-500 mb-0">
          Max 4MB. Allowed: JPG, PNG{fileTypes.includes("pdf") && ", PDF"}
        </p>
      </div>
    </div>
  );
};

export default FileUploader;
