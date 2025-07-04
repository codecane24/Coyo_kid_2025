// src/services/userService.ts
import axiosInstance from "../utils/axiosInstance";

export const getClassMaster= async () => {
  const response = await axiosInstance.get("/classesmaster");
  return response.data;
};
export const getClassesList = async () => {
  const response = await axiosInstance.get("/classes");
  return response.data;
};

export const getSection = async () => {
  const response = await axiosInstance.get("/section");
  return response.data;
};

export const createClass = async (classData: any) => {
  const response = await axiosInstance.post("/classes", classData);
  return response.data;
};