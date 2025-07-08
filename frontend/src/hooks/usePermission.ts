import { PermissionLogic } from "../config/PermissionLogic";
export const usePermission = () => {
  const rawPermissions = JSON.parse(localStorage.getItem("permissions") || "[]");

  const hasSidebarAccess = (labelOrKey: string) => {
    const directMatch = rawPermissions.includes(labelOrKey);

    const logic = PermissionLogic.find(
      (p) => p.type === "sidebar" && p.target === labelOrKey
    );

    if (!logic) return directMatch;

    return rawPermissions.includes(logic.key);
  };

  return { hasSidebarAccess };
};




