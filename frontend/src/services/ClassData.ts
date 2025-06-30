// src/services/userService.ts
import axiosInstance from "../utils/axiosInstance";

export const getClassesList = async () => {
  const response = await axiosInstance.get("/classes");
  return response.data;
};

// export const createClass = async (classData: any) => {
//   const response = await axiosInstance.post("/classes", classData);
//   return response.data;
// };
// export const updateClass = async (id: string, updatedData: any) => {
//   const response = await axiosInstance.put(`/classes/${id}`, updatedData);
//   return response.data;
// };
// export const deleteClass = async (id: string) => {
//   const response = await axiosInstance.delete(`/classes/${id}`);
//   return response.data;
// };

// export const getSection = async (id: string) => {
//   const response = await axiosInstance.get(`/users/${id}`);
//   return response.data;
// };
