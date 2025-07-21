// src/utils/axiosInstance.ts
import axios from "axios";

const axiosInstance = axios.create({
  baseURL: "https://coyokid.abbangles.com/backend/api/v1/", // ✅ Laravel backend base URL
  headers: {
    "Content-Type": "application/json", // ✅ For JSON and file uploads
     // ✅ For file uploads
  },
});



axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    const companyId = localStorage.getItem("companyId");
    const branchId = localStorage.getItem("branchId");

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
      config.headers.companyId = companyId;
      config.headers.branchId = branchId;

      // Logs for debugging
      console.log("📤 Sending headers:");
      console.log("Bearer Token:", token);
      console.log("School ID:", companyId);
      console.log("Branch ID:", branchId);
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
