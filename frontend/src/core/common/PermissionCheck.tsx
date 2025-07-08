import React, { ReactNode } from "react";
import { usePermissions } from "../../context/PermissionsContext";

interface Props {
  permission: string;
  children: ReactNode;
  fallback?: ReactNode;
}

const PermissionCheck = ({ permission, children, fallback = null }: Props) => {
  const { permissions } = usePermissions();

  return permissions.includes(permission) ? <>{children}</> : <>{fallback}</>;
};

export default PermissionCheck;