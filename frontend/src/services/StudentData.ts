// src/services/Student.ts
import axiosInstance from "../utils/axiosInstance";

// Get all students
export const getStudent = async () => {
  const response = await axiosInstance.get("/student");
  return response.data;
};

// Create student (handles JSON or FormData)
export const createStudent = async (payload: any) => {
  const isFormData = payload instanceof FormData;
  const response = await axiosInstance.post("/student", payload, {
    headers: isFormData ? { "Content-Type": "multipart/form-data" } : {},
  });
  return response.data;
};

// Update student — supports files (FormData) using method spoofing
export const updateStudent = async (studentId: string, payload: any) => {
  const isFormData = payload instanceof FormData;

  if (isFormData) {
    // Laravel can't handle PUT with multipart directly — spoof it
    payload.append("_method", "PUT");
    const response = await axiosInstance.post(`/student/${studentId}`, payload, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    return response.data;
  } else {
    // For plain JSON updates
    const response = await axiosInstance.put(`/student/${studentId}`, payload);
    return response.data;
  }
};
