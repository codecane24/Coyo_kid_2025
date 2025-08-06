// utils/buildExportColumns.ts

export const buildExportColumns = (columns: any[]) => {
  return columns
    .filter(col => col.title && col.title !== "Actions") // skip action column
    .map(col => {
      if (col.dataIndex) {
        return {
          title: col.title,
          field: col.dataIndex,
        };
      }

      // Special case: "Name" column has no dataIndex
      if (col.title === "Name") {
        return {
          title: "Name",
          field: "name",
        };
      }

      // For columns like "Section" that are static
      return {
        title: col.title,
        field: col.title.toLowerCase().replace(/\s/g, "_"),
      };
    });
};
