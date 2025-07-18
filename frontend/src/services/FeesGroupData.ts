import axiosInstance from "../utils/axiosInstance";

export const getFeesGroupList = async () => {
  const response = await axiosInstance.get("/fees-group");
  return response.data;
};

export const createFeesGroup = async (feesGroupData: any) => {
  const response = await axiosInstance.post("/fees-group", feesGroupData);
  return response.data;
};

export const updateFeesGroup = async (id: string, feesGroupData: any) => {
  const response = await axiosInstance.put(`/fees-group/${id}`, feesGroupData);
  return response.data;
};

export const deleteFeesGroup = async (id: string) => {
  const response = await axiosInstance.delete(`/fees-group/${id}`);
  return response.data;
};