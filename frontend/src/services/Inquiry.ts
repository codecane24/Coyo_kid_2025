// src/services/Branch.ts
import axiosInstance from "../utils/axiosInstance";


export const createInquiry = async (classData: any) => {
  const response = await axiosInstance.post("/branch", classData);
  return response.data;
};