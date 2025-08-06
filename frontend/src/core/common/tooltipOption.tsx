import React from "react";
import { OverlayTrigger, Tooltip } from "react-bootstrap";
import { Link } from "react-router-dom";
import { exportData } from "../../utils/exportHelper";

interface TooltipOptionProps {
  onRefresh?: () => void;
  onExport?: (type: "pdf" | "excel") => void;
  onPrint?: () => void;

  showRefresh?: boolean;
  showExport?: boolean;
  showPrint?: boolean;

  data?: any[];
  columns?: { title: string; field: string }[];
  exportFileName?: string;
}

const TooltipOption: React.FC<TooltipOptionProps> = ({
  onRefresh,
  onExport,
  onPrint,
  showRefresh = true,
  showExport = true,
  showPrint = true,
  data,
  columns,
  exportFileName = "export",
}) => {
  const handleInternalExport = (type: "pdf" | "excel") => {
    if (onExport) {
      onExport(type); // Use passed function if available
    } else if (data && columns && exportFileName) {
      exportData(type, data, columns, exportFileName); // Use fallback logic
    } else {
      console.warn("Export failed: missing onExport or export config.");
    }
  };

  return (
    <>
      {/* Refresh Button */}
      {showRefresh && (
        <div className="pe-1 mb-2">
          <OverlayTrigger
            placement="top"
            overlay={<Tooltip id="tooltip-top">Refresh</Tooltip>}
          >
            <button
              onClick={() => onRefresh?.()}
              className="btn btn-outline-light bg-white btn-icon me-1"
            >
              <i className="ti ti-refresh" />
            </button>
          </OverlayTrigger>
        </div>
      )}

      {/* Print Button */}
      {showPrint && (
        <div className="pe-1 mb-2">
          <OverlayTrigger
            placement="top"
            overlay={<Tooltip id="tooltip-top">Print</Tooltip>}
          >
            <button
              type="button"
              onClick={() => onPrint?.()}
              className="btn btn-outline-light bg-white btn-icon me-1"
            >
              <i className="ti ti-printer" />
            </button>
          </OverlayTrigger>
        </div>
      )}

      {/* Export Dropdown */}
      {showExport && (
        <div className="dropdown me-2 mb-2">
          <Link
            to="#"
            className="dropdown-toggle btn btn-light fw-medium d-inline-flex align-items-center"
            data-bs-toggle="dropdown"
          >
            <i className="ti ti-file-export me-2" />
            Export
          </Link>
          <ul className="dropdown-menu dropdown-menu-end p-3">
            <li>
              <Link
                to="#"
                onClick={() => handleInternalExport("pdf")}
                className="dropdown-item rounded-1"
              >
                <i className="ti ti-file-type-pdf me-1" />
                Export as PDF
              </Link>
            </li>
            <li>
              <Link
                to="#"
                onClick={() => handleInternalExport("excel")}
                className="dropdown-item rounded-1"
              >
                <i className="ti ti-file-type-xls me-1" />
                Export as Excel
              </Link>
            </li>
          </ul>
        </div>
      )}
    </>
  );
};

export default TooltipOption;
