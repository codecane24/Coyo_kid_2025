import React, { useEffect, useState } from "react";
import { Table } from "antd";
import Skeleton from "react-loading-skeleton";
import 'react-loading-skeleton/dist/skeleton.css';

type DatatableProps = {
  columns: any[];
  dataSource: any[];
  Selection?: boolean;
};

const Datatable: React.FC<DatatableProps> = ({ columns, dataSource, Selection }) => {
  const [selectedRowKeys, setSelectedRowKeys] = useState<any[]>([]);
  const [searchText, setSearchText] = useState<string>("");
  const [Selections, setSelections] = useState<any>(true);
  const [filteredDataSource, setFilteredDataSource] = useState(dataSource);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    setFilteredDataSource(dataSource);
    setIsLoading(dataSource.length === 0); // basic loading check
  }, [dataSource]);

  const onSelectChange = (newSelectedRowKeys: any[]) => {
    setSelectedRowKeys(newSelectedRowKeys);
  };

  const handleSearch = (value: string) => {
    setSearchText(value);
    const filteredData = dataSource.filter((record) =>
      Object.values(record).some((field) =>
        String(field).toLowerCase().includes(value.toLowerCase())
      )
    );
    setFilteredDataSource(filteredData);
  };

  const rowSelection = {
    selectedRowKeys,
    onChange: onSelectChange,
  };

  useEffect(() => {
    setSelections(Selection);
  }, [Selection]);

  // 📦 Skeleton Placeholder Rows
  const renderSkeletonRows = () => {
    return (
      <div className="px-3">
        {Array(6).fill(null).map((_, i) => (
          <div className="mb-2" key={i}>
            <Skeleton height={40} />
          </div>
        ))}
      </div>
    );
  };

  return (
    <>
      <div className="table-top-data d-flex px-3 justify-content-between">
        <div className="page-range"></div>
        <div className="serch-global text-right">
          <input
            type="search"
            className="form-control form-control-sm mb-3 w-auto float-end"
            value={searchText}
            placeholder="Search"
            onChange={(e) => handleSearch(e.target.value)}
            aria-controls="DataTables_Table_0"
          />
        </div>
      </div>

      {isLoading ? (
        renderSkeletonRows()
      ) : (
        Selections ? (
          <Table
            className="table datanew dataTable no-footer"
            rowSelection={rowSelection}
            columns={columns}
            rowHoverable={false}
            dataSource={filteredDataSource}
            pagination={{
              locale: { items_per_page: "" },
              nextIcon: <span>Next</span>,
              prevIcon: <span>Prev</span>,
              defaultPageSize: 10,
              showSizeChanger: true,
              pageSizeOptions: ["10", "20", "30"],
            }}
          />
        ) : (
          <Table
            className="table datanew dataTable no-footer"
            columns={columns}
            rowHoverable={false}
            dataSource={filteredDataSource}
            pagination={{
              locale: { items_per_page: "" },
              nextIcon: <span>Next</span>,
              prevIcon: <span>Prev</span>,
              defaultPageSize: 10,
              showSizeChanger: true,
              pageSizeOptions: ["10", "20", "30"],
            }}
          />
        )
      )}
    </>
  );
};

export default Datatable;
