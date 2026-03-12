import React from "react";
import { dragAndDropItems } from "helpers/dragDropItems";
import PrintItem from "./PrintItem";

import { connect } from "react-redux";

const DisplayItems = React.memo((props: any) => {
  const {
    dataRows,
    styles,
    products,
    orderFormId,
    fetchProducts,
    readableStyles,
  } = props;
  const data: any = dragAndDropItems;

  if (dataRows === undefined) return <></>;
  else
    return (
      <div>
        {Object.keys(dataRows["rows"]).map((row: any, index: any) => {
          const rowId = dataRows["rows"][row]["rowId"];
          const rowStyles = styles[rowId] !== undefined ? styles[rowId] : {};
          const columns = dataRows["rows"][row]["columns"];

          return (
            <div
              key={index}
              className={`form-row ${rowId} ${
                columns.length > 1
                  ? "multiple-column column-" + columns.length
                  : "one-column"
              }`}
              style={{
                ...readableStyles({ styles: rowStyles["box"] }),
              }}
            >
              {Object.keys(columns).map((col: any, index: any) => {
                const colId = columns[col]["colId"];
                const colStyles =
                  styles[colId] !== undefined ? styles[colId] : {};
                const items = dataRows["rows"][row]["columns"][col]["itemIds"];
                const readableColStyles = readableStyles({
                  styles: colStyles["box"],
                });
                let hasCustomWidth = false;

                if (
                  readableColStyles &&
                  typeof readableColStyles["width"] !== "undefined"
                ) {
                  if (readableColStyles["width"].indexOf("px") >= 0) {
                    const val = parseInt(readableColStyles["width"]);
                    if (val) hasCustomWidth = true;
                  }
                }

                let textAlignValue: string = "left";
                if (
                  colStyles["box"] !== undefined &&
                  colStyles["box"]["justifyContent"] !== undefined
                ) {
                  let justifyContent = colStyles["box"]["justifyContent"];
                  switch (justifyContent) {
                    case "flex-start":
                      textAlignValue = "left";
                      break;
                    case "flex-end":
                      textAlignValue = "right";
                      break;
                    case "center":
                      textAlignValue = "center";
                      break;
                    default:
                      break;
                  }
                }
                let textAlignCSS: any = {
                  textAlign: textAlignValue,
                };

                return (
                  <div
                    className={`wwof-col ${col}`}
                    key={index}
                    style={{
                      ...readableColStyles,
                    }}
                  >
                    <div
                      className={`drop-item ${
                        items.length > 0 ? "has-items" : "no-item"
                      }`}
                      style={{
                        ...readableColStyles,
                      }}
                    >
                      {items.map((itemKey: any, index: any) => {
                        const item = data["items"][itemKey];
                        const printItemProps = {
                          item,
                          styles: readableStyles({
                            styles: colStyles["element"],
                          }),
                          properties: colStyles["props"],
                          products,
                          orderFormId,
                          fetchProducts,
                        };

                        return (
                          <div
                            key={index}
                            className={`wwof-col ${col} item`}
                            style={{
                              ...readableColStyles,
                              ...textAlignCSS,
                            }}
                          >
                            <PrintItem {...printItemProps} />
                          </div>
                        );
                      })}
                    </div>
                  </div>
                );
              })}
            </div>
          );
        })}
      </div>
    );
});

const mapStateToProps = (store: any, props: any) => ({});

export default connect(mapStateToProps)(DisplayItems);
