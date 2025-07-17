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

  if (type === "excel") {
    const sheetData = [
      columns.map(col => col.title), // header
      ...data.map(row => columns.map(col => row[col.field])) // rows
    ];

    const worksheet = XLSX.utils.aoa_to_sheet(sheetData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet1");
    XLSX.writeFile(workbook, `${fileName}.xlsx`);
  }

  if (type === "pdf") {
    const doc = new jsPDF();

    autoTable(doc, {
      head: [columns.map(col => col.title)],
      body: data.map(row => columns.map(col => row[col.field])),
      styles: { fontSize: 10 },
    });

    doc.save(`${fileName}.pdf`);
  }
};
