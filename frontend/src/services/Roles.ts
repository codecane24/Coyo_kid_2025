// src/services/Roles.ts
import axiosInstance from "../utils/axiosInstance";

export const getRolelist= async () => {
  const response = await axiosInstance.get("/role");
  return response.data;
};

export const createClass = async (classData: any) => {
  const response = await axiosInstance.post("/role", classData);
  return response.data;
};