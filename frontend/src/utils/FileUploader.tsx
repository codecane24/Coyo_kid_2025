import React, { useEffect, useState } from "react";
import Link from "antd/es/typography/Link";
import { buildImageUrl } from "./buildImageUrl";

interface FileUploaderProps {
  fileTypes?: string;
  previewType?: "image" | "both";
  onFileChange: (file: File | null) => void;
  file: File | null;
  imageUrl?: string; // existing uploaded path, like `user/image.jpg`
}

const FileUploader: React.FC<FileUploaderProps> = ({
  fileTypes = "image/*",
  previewType = "image",
  onFileChange,
  file,
  imageUrl,
}) => {
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    console.log("FileUploader useEffect", {
      file: file ? file.name : null,
      imageUrl,
    });

    // Case 1: Local file selected
    if (file) {
      console.log("Setting blob URL for file:", file.name);
      const objectUrl = URL.createObjectURL(file);
      setPreviewUrl(objectUrl);
      setError(null);

      return () => {
        console.log("Revoking blob URL:", objectUrl);
        URL.revokeObjectURL(objectUrl);
      };
    }
    // Case 2: API image URL (edit case)
    else if (imageUrl) {
      const fullUrl = buildImageUrl(imageUrl);
      console.log("Setting API URL:", fullUrl);
      if (!fullUrl) {
        setError("Invalid image URL");
        setPreviewUrl(null);
      } else {
        setPreviewUrl(fullUrl);
        setError(null);
      }
    }
    // Case 3: No file or imageUrl
    else {
      console.log("No file or imageUrl, clearing preview");
      setPreviewUrl(null);
      setError(null);
    }
  }, [file, imageUrl]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selected = e.target.files?.[0] || null;
    if (selected) {
      console.log("File selected:", selected.name);
      if (selected.size > 4 * 1024 * 1024) {
        setError("File size exceeds 4MB limit");
        onFileChange(null);
        return;
      }
    }
    onFileChange(selected);
  };

  const handleRemove = () => {
    console.log("Removing file and clearing preview");
    onFileChange(null);
    setPreviewUrl(null);
    setError(null);
  };

  const handleImageError = () => {
    console.error("Image failed to load:", previewUrl);
    setError("Failed to load image");
    setPreviewUrl(null);
  };

  const isImage = file?.type?.startsWith("image/");

  return (
    <div className="d-flex align-items-center">
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
            onError={handleImageError}
          />
        ) : (
          <i className="ti ti-photo-plus fs-16" />
        )}
      </div>

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

        {error && <p className="text-danger fs-12 mt-1">{error}</p>}

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