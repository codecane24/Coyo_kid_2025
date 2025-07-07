// src/services/Branch.ts
import axiosInstance from "../utils/axiosInstance";

export const getRolelist= async () => {
  const response = await axiosInstance.get("/branch");
  return response.data;
};

export const createClass = async (classData: any) => {
  const response = await axiosInstance.post("/branch", classData);
  return response.data;
};