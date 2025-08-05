// src/components/common/WithRefresh.tsx
import React from "react";
import { useRefresh } from "../../context/RefreshContext";

const WithRefresh = ({ children }: { children: React.ReactNode }) => {
  const { refreshKey } = useRefresh();

  return <div key={refreshKey}>{children}</div>;
};

export default WithRefresh;
