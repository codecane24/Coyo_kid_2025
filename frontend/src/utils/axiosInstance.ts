// src/utils/axiosInstance.ts
import axios from "axios";
import { API_BASE_URL } from "../config/config";

const axiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
  },
});

// Optional: Add interceptors if needed
// axiosInstance.interceptors.request...

export default axiosInstance;
