// src/utils/axiosInstance.ts
import axios from "axios";

const axiosInstance = axios.create({
  baseURL: "https://your-api-url.com/api", // ✅ Your Laravel backend base URL
  headers: {
    "Content-Type": "application/json",
  },
});

axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
   if (error.response?.status === 401) {
  localStorage.removeItem("authToken");
  localStorage.removeItem("user");
  localStorage.removeItem("selectedBranch");
  // ❌ Don't redirect here; let the ProtectedRoute handle it
}

    return Promise.reject(error);
  }
);

export default axiosInstance;
