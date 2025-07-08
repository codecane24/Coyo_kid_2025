interface PermissionRule {
  key: string;
  type: "sidebar" | "route" | "button" | "section";
  target: string; // could be route path or UI key
}

export const permissionMap: PermissionRule[] = [
  { key: "view_dashboard", type: "route", target: "/dashboard" },
  { key: "view_users", type: "sidebar", target: "UsersMenu" },
  { key: "edit_users", type: "button", target: "EditUserButton" },
  { key: "access_sidebar_reports", type: "sidebar", target: "ReportsMenu" },
  // ➕ Add more here
];
