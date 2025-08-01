import React, { useEffect, useState } from "react";
import { buildImageUrl } from "./buildImageUrl";
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
    // ✅ This is the actual case from API — use the full URL
    setPreviewUrl(buildImageUrl(file));
  } else {
    // This is for local preview before uploading
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

  return (
    <div>
      <input
        type="file"
        accept={fileTypes || "image/*"}
        onChange={handleChange}
      />

      {previewType === "image" && previewUrl && (
        <img
          src={previewUrl}
          alt="preview"
          style={{
            width: "100%",
            height: "100%",
            objectFit: "cover",
            borderRadius: "8px",
            marginTop: "10px",
          }}
        />
      )}
    </div>
  );
};

export default FileUploader;
