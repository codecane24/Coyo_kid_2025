// src/services/User.ts
import axiosInstance from "../utils/axiosInstance";

export const getUser= async () => {
  const response = await axiosInstance.get("/user");
  return response.data;
};
export const createUser = (formData: FormData) => {
  return axiosInstance.post("/user", formData, {
    headers: {
      "Content-Type": "multipart/form-data", // ✅ Required for file uploads
    },
  });
}

export const updateUser = (id: string | number, data: any) => {
  return axiosInstance.put(`/user/${id}`, data);
};

export const getUserById = (id: string | number) => {
  return axiosInstance.get(`/user/${id}`);
};