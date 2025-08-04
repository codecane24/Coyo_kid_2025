
import axiosInstance from "../utils/axiosInstance";

export const getInquiryById = async (id: string) => {
  const response = await axiosInstance.get(`/admission-inquiry/${id}`);
  return response.data;
};

export const getInquiryList = async () => {
  const response = await axiosInstance.get("/admission-inquiry");
  return response.data;
};

export const createInquiry = async (inquiryData: any) => {
  const response = await axiosInstance.post("/admission-inquiry", inquiryData);
  return response.data;
};

export const updateInquiry = async (id: string, inquiryData: any) => {
  const response = await axiosInstance.put(`/admission-inquiry/${id}`, inquiryData);
  return response.data;
};

export const deleteInquiry = async (id: string) => {
  const response = await axiosInstance.delete(`/admission-inquiry/${id}`);
  return response.data;
};