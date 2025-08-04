
import axiosInstance from "../utils/axiosInstance";

export const getInquiryById = async (id: string) => {
  const response = await axiosInstance.get(`/inquiry/${id}`);
  return response.data;
};

export const getInquiryList = async () => {
  const response = await axiosInstance.get("/inquiry");
  return response.data;
};

export const createInquiry = async (inquiryData: any) => {
  const response = await axiosInstance.post("/inquiry", inquiryData);
  return response.data;
};

export const updateInquiry = async (id: string, inquiryData: any) => {
  const response = await axiosInstance.put(`/inquiry/${id}`, inquiryData);
  return response.data;
};

export const deleteInquiry = async (id: string) => {
  const response = await axiosInstance.delete(`/inquiry/${id}`);
  return response.data;
};