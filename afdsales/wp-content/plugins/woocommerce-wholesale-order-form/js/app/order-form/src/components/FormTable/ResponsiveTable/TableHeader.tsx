import { Thead, Tr, Th } from "react-super-responsive-table";
import { connect } from "react-redux";

const TableHeader = (props: any) => {
  const {
    getPropValue,
    pageHasVariations,
    formStyles,
    formTable,
    tableBodyStrings,
  } = props;

  if (typeof formTable.itemIds !== "undefined") {
    const tableRows = formTable.itemIds.map((column: string, i: number) => {
      switch (column) {
        case "product-image":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "product-name":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "sku":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "in-stock-amount":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "price":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "quantity-input":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "add-to-cart-button":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "variation-dropdown":
          let smartVisibility = getPropValue({
            formStyles,
            column,
            prop: "smartVisibility",
          });
          if (smartVisibility === undefined || smartVisibility === null) {
            smartVisibility = true;
          }

          if (!pageHasVariations && smartVisibility) {
            return <Th key={i}>&nbsp;</Th>;
          } else {
            return (
              <Th key={i} className="variation-dropdown">
                {getPropValue({
                  formStyles,
                  column,
                  prop: "columnHeading",
                }) || tableBodyStrings?.[column]}
              </Th>
            );
          }

        case "product-meta":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        // case "global-attribute":
        //   return (
        //     <Th key={i}>
        //       {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
        //         ""}
        //     </Th>
        //   );

        case "short-description":
          return (
            <Th key={i}>
              {getPropValue({ formStyles, column, prop: "columnHeading" }) ||
                tableBodyStrings?.[column]}
            </Th>
          );

        case "add-to-cart-checkbox":
          return <Th key={i}>&nbsp;</Th>;

        default:
          return <Th key={i}>&nbsp;</Th>;
      }
    });
    return (
      <Thead>
        <Tr>{tableRows}</Tr>
      </Thead>
    );
  } else {
    return <></>;
  }
};

const mapStateToProps = (store: any, props: any) => ({
  tableBodyStrings: store.i18n.frontend.table_column_headers,
});

export default connect(mapStateToProps)(TableHeader);
