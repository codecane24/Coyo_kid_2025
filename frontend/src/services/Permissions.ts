// src/services/Permissions.ts
import axiosInstance from "../utils/axiosInstance";

export const getPermissionsList= async () => {
  const response = await axiosInstance.get("/permission");
  return response.data;
};

export const createPermmisions = async (classData: any) => {
  const response = await axiosInstance.post("/permission", classData);
  return response.data;
};