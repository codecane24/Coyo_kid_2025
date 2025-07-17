export const printElementById = (elementId: string) => {
  const content = document.getElementById(elementId);
  if (!content) {
    console.warn(`Element with ID "${elementId}" not found.`);
    return;
  }

  const printWindow = window.open("", "_blank");
  if (!printWindow) return;

  printWindow.document.write(`
    <html>
      <head>
        <title>Print</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
        </style>
      </head>
      <body>
        ${content.innerHTML}
      </body>
    </html>
  `);

  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
};
