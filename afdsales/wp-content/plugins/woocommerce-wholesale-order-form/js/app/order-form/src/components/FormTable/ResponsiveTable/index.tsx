import { Table } from "react-super-responsive-table";
import "react-super-responsive-table/dist/SuperResponsiveTableStyle.css";
import TableHeader from "./TableHeader";
import TableBody from "./TableBody";
import SortableColumns from "./SortableColumns";
import TableSkeleton from "./TableSkeleton";
import SelectAll from "./SelectAll";

const ResponsiveTable = (props: any) => {
  const { fetching, orderFormId } = props;

  if (fetching) {
    return (
      <div className="responsive-table-wrapper">
        <TableSkeleton />
      </div>
    );
  } else {
    return (
      <div
        className={`responsive-table-wrapper order-form-table-${orderFormId}`}
      >
        <SortableColumns {...props} />
        <SelectAll {...props} />
        <Table
          className={`order-form-responsive-table loaded`}
          style={{ display: "none" }}
        >
          <TableHeader {...props} />
          <TableBody {...props} />
        </Table>
        <SelectAll {...props} />
      </div>
    );
  }
};

export default ResponsiveTable;
