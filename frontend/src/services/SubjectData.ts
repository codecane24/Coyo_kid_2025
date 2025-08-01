// src/services/ClassData.ts
import axiosInstance from "../utils/axiosInstance";

export const getSubjectList= async () => {
  const response = await axiosInstance.get("/subject");
  return response.data;
};


export const createSubject = async (subjectData: any) => {
  const response = await axiosInstance.post("/subject", subjectData);
  return response.data;
};

export const showSubjectData = async (id: string) => {
  const response = await axiosInstance.get(`/subject/${id}`);
  return response.data;
}

export const updateSubject = async (id: string, subjectData: any) => {
  const response = await axiosInstance.put(`/subject/${id}`, subjectData);
  return response.data;
};

export const deleteSubject = async (id: string) => {
  const response = await axiosInstance.delete(`/subject/${id}`);
  return response.data;
};  