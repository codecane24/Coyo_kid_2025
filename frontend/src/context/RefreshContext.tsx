// src/context/RefreshContext.tsx
import React, { createContext, useContext, useState } from "react";

interface RefreshContextType {
  refreshKey: number;
  refresh: () => void;
}

const RefreshContext = createContext<RefreshContextType | undefined>(undefined);

export const RefreshProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [refreshKey, setRefreshKey] = useState(0);

  const refresh = () => setRefreshKey(prev => prev + 1);

  return (
    <RefreshContext.Provider value={{ refreshKey, refresh }}>
      {children}
    </RefreshContext.Provider>
  );
};

// Custom hook to use in any component
export const useRefresh = () => {
  const context = useContext(RefreshContext);
  if (!context) throw new Error("useRefresh must be used within RefreshProvider");
  return context;
};
