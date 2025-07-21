import axiosInstance from "../utils/axiosInstance";

export const getTeacherList = async () => {
  const response = await axiosInstance.get("/teacher");
  return response.data;
};

export const createTeacher = async (teacherData: any) => {
  const response = await axiosInstance.post("/teacher", teacherData);
  return response.data;
};

export const updateTeacher = async (id: string, teacherData: any) => {
  const response = await axiosInstance.put(`/teacher/${id}`, teacherData);
  return response.data;
};

export const deleteTeacher = async (id: string) => {
  const response = await axiosInstance.delete(`/teacher/${id}`);
  return response.data;
};