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

//== class fees master
export const getClassFeesMasterList = async () => {
  const response = await axiosInstance.get("/class-fees");
  return response.data;
}

export const getClassFeesMasterById = async (id: string) => {
  const response = await axiosInstance.get(`/class-fees/${id}`);
  return response.data;
};

export const createClassFeesMaster = async (classFeesMasterData: any) => {
  const response = await axiosInstance.post("/class-fees", classFeesMasterData);
  return response.data;
};

export const updateClassFeesMaster = async (id: string, classFeesMasterData: any) => {
  const response = await axiosInstance.put(`/class-fees/${id}`, classFeesMasterData);
  return response.data;
};

export const deleteClassFeesMaster = async (id: string) => {
  const response = await axiosInstance.delete(`/class-fees/${id}`);
  return response.data;
};




// Utility for select dropdown options (dynamic from getFeesTypeList)
export const getFeesTypeDropdown = async (defaultValue?: string) => {
  const res = await getFeesTypeList();
  const data = Array.isArray(res.data) ? res.data : [];
  interface FeesType {
    id: string;
    name: string;
    group_id: string;
    feesgroup: {
      id: string;
      name: string;
    };
  }

  interface DropdownOption {
    value: string;
    label: string;
    name: string;
    group_id?: string; // Optional, if you want to include group ID;
    group_name?: string; // Optional, if you want to include group name
  }

  const options: DropdownOption[] = (data as FeesType[]).map((item: FeesType) => ({
    value: item.id,
    label: item.name,
    name: item.name,
    group_id: item.feesgroup.id, // Optional, if you want to include group ID
    group_name: item.feesgroup.name, // Optional, if you want to include group name
  }));
  const defaultOption = defaultValue
    ? options.find((opt: DropdownOption) => String(opt.value) === String(defaultValue))
    : undefined;
  return { options, defaultOption };
};

