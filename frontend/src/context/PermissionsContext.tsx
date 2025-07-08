import React, { createContext, useContext, ReactNode } from "react";
import { useAuth } from "./AuthContext";

interface PermissionsContextProps {
  permissions: string[];
}

const PermissionsContext = createContext<PermissionsContextProps>({
  permissions: [],
});

export const usePermissions = () => useContext(PermissionsContext);

export const PermissionsProvider = ({ children }: { children: ReactNode }) => {
  const { user } = useAuth();

  const permissions = user?.permissions || [];

  return (
    <PermissionsContext.Provider value={{ permissions }}>
      {children}
    </PermissionsContext.Provider>
  );
};
