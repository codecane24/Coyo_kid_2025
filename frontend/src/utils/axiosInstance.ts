// src/utils/axiosInstance.ts
import axios from "axios";

const axiosInstance = axios.create({
  baseURL: "https://coyokid.abbangles.com/backend/api/v1/", // ✅ Laravel backend base URL
  headers: {
    "Content-Type": "application/json",
  },
});

// 🔐 Add token to every request
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    if (token) {
       console.log("🔐 Token being sent:", token); // <--- Add this
      config.headers.Authorization = `Bearer ${token}`;
         config.headers.mytoken = token;
         console.log("My Token being sent:",config.headers.mytoken)
      
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// ❌ Handle unauthorized responses
axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Remove user-related data
      localStorage.removeItem("authToken");
      localStorage.removeItem("user");
      localStorage.removeItem("selectedBranch");
      // ❌ Don't redirect here — let ProtectedRoute handle it
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;
