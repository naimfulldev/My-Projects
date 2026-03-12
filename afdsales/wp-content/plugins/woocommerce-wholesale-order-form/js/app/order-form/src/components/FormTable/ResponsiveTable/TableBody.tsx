import { useEffect, useState } from "react";
import { Tbody, Tr, Td } from "react-super-responsive-table";
import PrintItem from "../PrintItem";
import { connect } from "react-redux";

const TableBody = (props: any) => {
  const {
    orderFormId,
    getPropValue,
    pageHasVariations,
    useStyleValue,
    somethingChanged,
    selectedAll,
    formProducts,
    formStyles,
    formTable,
    modalProps,
    setModalProps,
    tableBodyStrings,
  } = props;

  const [tableColumns, setTableColumns] = useState<JSX.Element[]>([]);

  useEffect(() => {
    if (formProducts.length > 0) {
      let data = formProducts.map((product: any, i: number) => {
        const printItemProps = {
          orderFormId,
          product,
          getPropValue,
          formStyles,
          useStyleValue,
        };

        const tableRows = formTable.itemIds.map((column: string, j: number) => {
          switch (column) {
            case "product-image":
              return (
                <Td key={j}>
                  <PrintItem
                    {...printItemProps}
                    itemId="product-image"
                    modalProps={modalProps}
                    setModalProps={setModalProps}
                  />
                </Td>
              );

            case "product-name":
              return (
                <Td key={j}>
                  <PrintItem
                    {...printItemProps}
                    itemId="product-name"
                    modalProps={modalProps}
                    setModalProps={setModalProps}
                  />
                </Td>
              );

            case "sku":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="sku" />
                </Td>
              );

            case "in-stock-amount":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="in-stock-amount" />
                </Td>
              );

            case "price":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="price" />
                </Td>
              );

            case "quantity-input":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="quantity-input" />
                </Td>
              );

            case "add-to-cart-button":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="add-to-cart-button" />
                </Td>
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
                return <Td key={i}>&nbsp;</Td>;
              } else {
                return (
                  <Td key={j} className="variation-dropdown">
                    <b style={{ paddingBottom: "8px", display: "block" }}>
                      {getPropValue({
                        formStyles,
                        column,
                        prop: "columnHeading",
                      }) || tableBodyStrings?.[column]}
                      :
                    </b>
                    <PrintItem
                      {...printItemProps}
                      itemId="variation-dropdown"
                    />
                  </Td>
                );
              }

            case "product-meta":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="product-meta" />
                </Td>
              );

            case "global-attribute":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="global-attribute" />
                </Td>
              );

            case "short-description":
              return (
                <Td key={j}>
                  <PrintItem {...printItemProps} itemId="short-description" />
                </Td>
              );

            case "add-to-cart-checkbox":
              return (
                <Td key={j}>
                  <PrintItem
                    {...printItemProps}
                    selectedAll={selectedAll}
                    somethingChanged={somethingChanged}
                    itemId="add-to-cart-checkbox"
                  />
                </Td>
              );
          }
        });
        return <Tr key={i}>{tableRows}</Tr>;
      });
      setTableColumns(data);
    }
  }, [formProducts, formTable, pageHasVariations, getPropValue]);

  return <Tbody>{tableColumns}</Tbody>;
};

const mapStateToProps = (store: any, props: any) => ({
  tableBodyStrings: store.i18n.frontend.table_column_headers,
});

export default connect(mapStateToProps)(TableBody);
