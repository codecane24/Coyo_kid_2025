// src/services/User.ts
import axiosInstance from "../utils/axiosInstance";

export const getUser= async () => {
  const response = await axiosInstance.get("/user");
  return response.data;
};

export const createUser = async (classData: any) => {
  const response = await axiosInstance.post("/user", classData);
  return response.data;
};