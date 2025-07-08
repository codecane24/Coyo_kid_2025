// context/PermissionContext.tsx
import React, { createContext, useContext } from "react";
import { useAuth } from "./AuthContext";

type PermissionType = "sidebar" | "route" | "button" | "page" | "custom";

interface PermissionContextProps {
  hasPermission: (perm: string, type?: PermissionType) => boolean;
}

const PermissionContext = createContext<PermissionContextProps | undefined>(undefined);

export const PermissionProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { user } = useAuth();
  const permissions: string[] = user?.permissions || [];

  const hasPermission = (perm: string, type: PermissionType = "custom") => {
    const fullPermissionName = `${type}:${perm}`; // e.g., sidebar:Dashboard
    return permissions.includes(fullPermissionName) || permissions.includes(perm);
  };

  return (
    <PermissionContext.Provider value={{ hasPermission }}>
      {children}
    </PermissionContext.Provider>
  );
};

export const usePermission = (): PermissionContextProps => {
  const context = useContext(PermissionContext);
  if (!context) {
    throw new Error("usePermission must be used within a PermissionProvider");
  }
  return context;
};
