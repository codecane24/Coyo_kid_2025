import axiosInstance from "../utils/axiosInstance";

export const getTeacherList = async () => {
  const response = await axiosInstance.get("/teachers");
  return response.data;
};

export const createTeacher = async (teacherData: any) => {
  const response = await axiosInstance.post("/teachers", teacherData);
  return response.data;
};

export const updateTeacher = async (id: string, teacherData: any) => {
  const response = await axiosInstance.put(`/teachers/${id}`, teacherData);
  return response.data;
};

export const deleteTeacher = async (id: string) => {
  const response = await axiosInstance.delete(`/teachers/${id}`);
  return response.data;
};