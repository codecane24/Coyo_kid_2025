// src/services/Roles.ts
import axiosInstance from "../utils/axiosInstance";

export const getRolelist= async () => {
  const response = await axiosInstance.get("/role");
  return response.data;
};


export const createRole = async (RoleData: any) => {
  const response = await axiosInstance.post("/role", RoleData);
  return response.data;
};