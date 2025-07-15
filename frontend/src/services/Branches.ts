// src/services/Branch.ts
import axiosInstance from "../utils/axiosInstance";

export const getBranch= async () => {
  const response = await axiosInstance.get("/branch");
  return response.data;
};

export const createBranch = async (classData: any) => {
  const response = await axiosInstance.post("/branch", classData);
  return response.data;
};