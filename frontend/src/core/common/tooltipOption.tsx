import React from "react";
import { OverlayTrigger, Tooltip } from "react-bootstrap";
import { Link } from "react-router-dom";
interface TooltipOptionProps {
  onRefresh?: () => void; // ✅ Optional prop
    onExport?: (type: "pdf" | "excel") => void;
      onPrint?: () => void;
}
const TooltipOption : React.FC<TooltipOptionProps> = ({ onRefresh, onExport ,onPrint}) => {
  return (
    <>
 <div className="pe-1 mb-2">
        <OverlayTrigger
          placement="top"
          overlay={<Tooltip id="tooltip-top">Refresh</Tooltip>}
        >
          <button
            onClick={() => {
              if (onRefresh) onRefresh(); // ✅ Only call if provided
            }}
            className="btn btn-outline-light bg-white btn-icon me-1"
          >
            <i className="ti ti-refresh" />
          </button>
        </OverlayTrigger>
      </div>

 {/* Print */}
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

      {/* Export Dropdown */}
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
              onClick={() => onExport?.("pdf")}
              className="dropdown-item rounded-1"
            >
              <i className="ti ti-file-type-pdf me-1" />
              Export as PDF
            </Link>
          </li>
          <li>
            <Link
              to="#"
              onClick={() => onExport?.("excel")}
              className="dropdown-item rounded-1"
            >
              <i className="ti ti-file-type-xls me-1" />
              Export as Excel
            </Link>
          </li>
        </ul>
      </div>
    </>
  );
};

export default TooltipOption;
