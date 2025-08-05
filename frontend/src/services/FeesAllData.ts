import axiosInstance from "../utils/axiosInstance";

export const getFeesGroupList = async () => {
  const response = await axiosInstance.get("/fees-group");
  return response.data;
};

export const getFeesGroupById = async (id: string) => {
  const response = await axiosInstance.get(`/fees-group/${id}`);
  return response.data;
};

export const createFeesGroup = async (feesGroupData: any) => {
  const response = await axiosInstance.post("/fees-group", feesGroupData);
  return response.data;
};

export const updateFeesGroup = async (id: string, feesGroupData: any) => {
  const response = await axiosInstance.put(`/fees-group/${id}`, feesGroupData);
  return response.data;
};

export const deleteFeesGroup = async (id: string) => {
  const response = await axiosInstance.delete(`/fees-group/${id}`);
  return response.data;
};

//==== Fees Types ====
export const getFeesTypeList = async () => {
  const response = await axiosInstance.get("/fees-type");
  return response.data;
};

export const createFeesType = async (feesTypeData: any) => {
  const response = await axiosInstance.post("/fees-type", feesTypeData);
  return response.data;
};

export const updateFeesType = async (id: string, feesTypeData: any) => {
  const response = await axiosInstance.put(`/fees-type/${id}`, feesTypeData);
  return response.data;
};

export const deleteFeesType = async (id: string) => {
  const response = await axiosInstance.delete(`/fees-type/${id}`);
  return response.data;
};

export const getFeesTypeById = async (id: string) => {
  const response = await axiosInstance.get(`/fees-type/${id}`);
  return response.data;
};



//===== Fees Master =====
export const getFeesMasterList = async () => {
  const response = await axiosInstance.get("/fees-master");
  return response.data;
};

export const getFeesMasterById = async (id: string) => {
  const response = await axiosInstance.get(`/fees-master/${id}`);
  return response.data;
};

export const createFeesMaster = async (feesMasterData: any) => {
  const response = await axiosInstance.post("/fees-master", feesMasterData);
  return response.data;
};

export const updateFeesMaster = async (id: string, feesMasterData: any) => {
  const response = await axiosInstance.put(`/fees-master/${id}`, feesMasterData);
  return response.data;
};

export const deleteFeesMaster = async (id: string) => {
  const response = await axiosInstance.delete(`/fees-master/${id}`);
  return response.data;
};  


//===== Fees Assign =====
export const getFeesAssignList = async () => {
  const response = await axiosInstance.get("/fees-assign");
  return response.data;
};

export const getFeesAssignById = async (id: string) => {
  const response = await axiosInstance.get(`/fees-assign/${id}`);
  return response.data;
};

export const createFeesAssign = async (feesAssignData: any) => {
  const response = await axiosInstance.post("/fees-assign", feesAssignData);
  return response.data;
};

export const updateFeesAssign = async (id: string, feesAssignData: any) => {
  const response = await axiosInstance.put(`/fees-assign/${id}`, feesAssignData);
  return response.data;
};

export const deleteFeesAssign = async (id: string) => {
  const response = await axiosInstance.delete(`/fees-assign/${id}`);
  return response.data;
};

export const getFeesAssignByStudentId = async (studentId: string) => {
  const response = await axiosInstance.get(`/fees-assign/student/${studentId}`);
  return response.data;
};

export const getFeesAssignByClassId = async (classId: string) => {
  const response = await axiosInstance.get(`/fees-assign/class/${classId}`);
  return response.data;
};

