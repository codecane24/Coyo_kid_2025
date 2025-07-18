// src/services/ClassData.ts
import axiosInstance from "../utils/axiosInstance";

export const getFeesGroupList = async () => {
  const response = await axiosInstance.get("/fees-group");
  return response.data;
};

export const createFeesGroup = async (feesGroupData: any) => {
  const response = await axiosInstance.post("/fees-group", feesGroupData);
  return response.data;
};