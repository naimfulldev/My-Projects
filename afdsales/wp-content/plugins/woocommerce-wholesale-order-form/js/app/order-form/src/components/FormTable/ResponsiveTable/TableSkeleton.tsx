import { Table, Thead, Tbody, Tr, Th, Td } from "react-super-responsive-table";
import { Skeleton } from "antd";

const TableSkeleton = (props: any) => {
  const tbody = [1, 2, 3].map((n: number) => {
    return (
      <Tr key={n}>
        <Td>
          <Skeleton active paragraph={{ rows: 1 }} />
        </Td>
        <Td>
          <Skeleton.Avatar active shape="square" size={48} />
        </Td>
        <Td>
          <Skeleton active paragraph={{ rows: 1 }} />
        </Td>
        <Td>
          <Skeleton.Button active size="large" />
        </Td>
      </Tr>
    );
  });
  return (
    <div className="table-skeleton" style={{ display: "none" }}>
      <div
        style={{
          display: "flex",
          gap: "8px",
          flexDirection: "column",
          padding: "4px",
          marginBottom: "10px",
        }}
      >
        <Skeleton.Input style={{ width: "100%" }} active size="large" />
        <Skeleton.Input style={{ width: "100%" }} active size="large" />
        <Skeleton active paragraph={{ rows: 1 }} />
      </div>

      <Table className="order-form-responsive-table loading">
        <Thead>
          <Tr>
            <Th>
              <Skeleton.Button active size="small" />
            </Th>
            <Th>
              <Skeleton.Button active size="small" />
            </Th>
            <Th>
              <Skeleton.Button active size="small" />
            </Th>
            <Th>
              <Skeleton.Button active size="small" />
            </Th>
          </Tr>
        </Thead>
        <Tbody>{tbody}</Tbody>
      </Table>
    </div>
  );
};
export default TableSkeleton;
