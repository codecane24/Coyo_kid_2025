// src/services/Branch.ts
import axiosInstance from "../utils/axiosInstance";

// Academic Year
export const getAcademicYearList = async () => {
  const response = await axiosInstance.get("/academic-year");
  return response.data;
}

export const getAcademicYearById = async (id: string) => {
  const response = await axiosInstance.get(`/academic-year/${id}`);
  return response.data;
}

export const createAcademicYear = async (academicYearData: any) => {
  const response = await axiosInstance.post("/academic-year", academicYearData);
  return response.data;
}
export const updateAcademicYear = async (id: string, academicYearData: any) => {
  const response = await axiosInstance.put(`/academic-year/${id}`, academicYearData);
  return response.data;
}

export const deleteAcademicYear = async (id: string) => {
  const response = await axiosInstance.delete(`/academic-year/${id}`);
  return response.data;
}

