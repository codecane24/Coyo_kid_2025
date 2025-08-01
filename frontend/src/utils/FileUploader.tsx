import React, { useEffect, useState } from "react";
import { buildImageUrl } from "./buildImageUrl";
import { Link } from "react-router-dom";

type FileUploaderProps = {
  file: File | string | null;
  fileTypes?: string;
  previewType?: "image";
  onFileChange: (file: File | null) => void;
};

const FileUploader: React.FC<FileUploaderProps> = ({
  file,
  fileTypes,
  previewType = "image",
  onFileChange,
}) => {
  const [previewUrl, setPreviewUrl] = useState<string>("");

  useEffect(() => {
    if (!file) {
      setPreviewUrl("");
      return;
    }

    if (typeof file === "string") {
      setPreviewUrl(buildImageUrl(file));
    } else {
      const url = URL.createObjectURL(file);
      setPreviewUrl(url);

      return () => {
        URL.revokeObjectURL(url);
      };
    }
  }, [file]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0] || null;
    onFileChange(selectedFile);
  };

  const handleRemove = () => {
    onFileChange(null);
  };

  return (
    <div className="profile-upload">
      <div className="profile-uploader d-flex align-items-center">
        <div className="drag-upload-btn mb-3 position-relative">
          Upload
          <input
            type="file"
            className="form-control image-sign"
            accept={fileTypes || "image/*"}
            onChange={handleChange}
            style={{
              position: "absolute",
              top: 0,
              left: 0,
              opacity: 0,
              width: "100%",
              height: "100%",
              cursor: "pointer",
            }}
          />
        </div>
        <Link
          to="#"
          className="btn btn-primary mb-3"
          onClick={handleRemove}
        >
          Remove
        </Link>
      </div>

      {previewType === "image" && previewUrl && (
        <img
          src={previewUrl}
          alt="preview"
          className="img-thumbnail mb-2"
          style={{ maxWidth: "200px", height: "auto" }}
        />
      )}

      <p className="fs-12">
        Upload image size 4MB, Format JPG, PNG, SVG
      </p>
    </div>
  );
};

export default FileUploader;
