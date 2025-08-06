import * as XLSX from "xlsx";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

export const exportData = (
  type: "pdf" | "excel",
  data: any[],
  columns: { title: string; field: string }[],
  fileName: string = "export"
) => {
  if (!data || data.length === 0) {
    console.warn("No data to export.");
    return;
  }

  const formatValue = (value: any, field: string, row: any) => {
    switch (field) {
      case "name":
        return `${row.first_name || ""} ${row.last_name || ""}`.trim() || "N/A";

      case "status":
        return value === 1 || value === "1" ? "Active" : "Inactive";

      case "gender":
        return value ? value.charAt(0).toUpperCase() + value.slice(1) : "N/A";

      case "doj":
      case "dob":
        try {
          const date = new Date(value);
          return isNaN(date.getTime()) ? "" : date.toLocaleDateString();
        } catch {
          return "";
        }

      case "section":
        return "N/A";

      default:
        return value ?? "N/A";
    }
  };

  if (type === "excel") {
    const sheetData = [
      columns.map(col => col.title),
      ...data.map(row =>
        columns.map(col => formatValue(row[col.field], col.field, row))
      ),
    ];

    const worksheet = XLSX.utils.aoa_to_sheet(sheetData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");
    XLSX.writeFile(workbook, `${fileName}.xlsx`);
  }

  if (type === "pdf") {
    const doc = new jsPDF("landscape");

    const headers = columns.map(col => col.title);
    const body = data.map(row =>
      columns.map(col => formatValue(row[col.field], col.field, row))
    );

    autoTable(doc, {
      head: [headers],
      body: body,
      startY: 20,
      styles: {
        fontSize: 10,
        cellPadding: 3,
        overflow: "linebreak",
      },
      headStyles: {
        fillColor: [3, 181, 139],
        textColor: [255, 255, 255],
        fontStyle: "bold",
        halign: "center",
      },
      bodyStyles: {
        valign: "middle",
      },
      theme: "grid",
      tableWidth: "auto",
      didDrawPage: function (data) {
        doc.setFontSize(14);
        doc.text(fileName, data.settings.margin.left, 15);
      },
    });

    doc.save(`${fileName}.pdf`);
  }
};
