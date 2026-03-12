import { Skeleton } from "antd";
import getComponentName from "helpers/getComponentName";

const TableSkeleton = (props: any) => {
  const { itemIds, styling } = props;

  const tableHeaders = itemIds.map((itemId: string, i: number) => {
    const properties =
      styling.styles[itemId] !== undefined
        ? styling.styles[itemId]["props"]
        : {};
    return (
      <th
        key={i}
        style={{
          padding: "16px",
          fontWeight: 500,
          paddingTop: "30px",
          background: "#fafafa",
        }}
      >
        {getComponentName({ itemId, properties })}
      </th>
    );
  });

  const tableBody = itemIds.map((itemId: string, i: number) => {
    switch (itemId) {
      case "product-image":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton.Avatar active shape="square" size={48} />
          </td>
        );
      case "variation-dropdown":
      case "product-data":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton.Input style={{ width: 200 }} active size="large" />
          </td>
        );
      case "quantity-input":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton.Input style={{ width: 100 }} active size="large" />
          </td>
        );

      case "add-to-cart-checkbox":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton.Button active size="small" />
          </td>
        );
      case "add-to-cart-button":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton.Button active size="large" />
          </td>
        );

      case "product-name":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton active paragraph={{ rows: 1 }} />
          </td>
        );
      case "short-description":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton active paragraph={{ rows: 4 }} />
          </td>
        );
      case "sku":
      case "in-stock-amount":
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton.Button active size="small" />
          </td>
        );
      default:
        return (
          <td key={i} style={{ padding: "16px" }}>
            <Skeleton active paragraph={{ rows: 1 }} />
          </td>
        );
    }
  });
  return (
    <div
      className="table-skeleton"
      style={{ border: "1px solid rgb(238, 238, 238)" }}
    >
      <table style={{ width: "100%", fontSize: "16px", textAlign: "center" }}>
        <thead>
          <tr>{tableHeaders}</tr>
        </thead>
        <tbody>
          <tr>{tableBody}</tr>
          <tr>{tableBody}</tr>
          <tr>{tableBody}</tr>
          <tr>{tableBody}</tr>
        </tbody>
      </table>
    </div>
  );
};
export default TableSkeleton;
