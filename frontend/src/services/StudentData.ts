// src/services/Student.ts
import axiosInstance from "../utils/axiosInstance";

export const getStudent= async () => {
  const response = await axiosInstance.get("/student");
  return response.data;
};


export const createStudent = async (RoleData: any) => {
  const response = await axiosInstance.post("/student", RoleData);
  return response.data;
};