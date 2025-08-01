import axiosInstance from "../utils/axiosInstance";

/**
 * Fetch serial number only if `existingId` is not available.
 * @param type - Type for dynamic API endpoint (e.g. 'student', 'teacher')
 * @param existingId - Optional. If provided, this will be returned instead of calling the API.
 */
export const getAllId = async (type: string, existingId?: string | null): Promise<string> => {
  // If an ID already exists, return it directly (avoid API call)
  if (existingId) {
    return existingId;
  }

  // Otherwise, fetch from API
  const response = await axiosInstance.get(`serialnumber/${type}`);
  
  // Return string (you can modify this based on your actual response shape)
  if (typeof response.data === "string") {
    return response.data;
  }

  // If API returns object, handle safely
  return response.data?.serialId || response.data?.id || "";
};
