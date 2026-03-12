import { useState, useEffect } from "react";
import { FrownOutlined, SmileOutlined } from "@ant-design/icons";
import { connect } from "react-redux";

const InStockAmount = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
    inStockAmountStrings,
  } = props;

  const [stockQuantity, setStockQuantity] = useState(product.stock_quantity);
  const [stockStatus, setStockStatus] = useState(product.stock_status);
  const [backorders, setBackorders] = useState(product.backorders);

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProducts !== "undefined" &&
        Object.keys(selectedProducts).length > 0
      ) {
        if (
          typeof selectedProduct !== "undefined" &&
          product.type === "variable"
        ) {
          const variationID = selectedProduct?.["variationID"];
          const variations =
            orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
              product.id
            ];

          if (typeof variations !== "undefined") {
            const variationData = variations.find((variation: any) => {
              return variation.id === variationID;
            });

            if (typeof variationData !== "undefined") {
              setStockQuantity(variationData.stock_quantity);
              setStockStatus(variationData.stock_status);
              setBackorders(variationData.backorders);
            }
          }
        }
      } else {
        setStockQuantity(product.stock_quantity);
        setStockStatus(product.stock_status);
        setBackorders(product.backorders);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  const alignment =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "justifyContent",
    }) || "center";
  const alignmentCSS = {
    textAlign:
      alignment === "flex-start"
        ? ("left" as const)
        : alignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  return (
    <div
      className="item instock-amount"
      style={{ minWidth: "100px", ...alignmentCSS }}
    >
      {stockStatus === "outofstock" ? (
        <div>
          <p className={stockStatus}>
            <FrownOutlined style={{ color: "#e2401c" }} />{" "}
            {inStockAmountStrings?.out_of_stock}
          </p>
        </div>
      ) : (
        <div>
          {stockQuantity === 0 && backorders === "notify" ? (
            <p className={stockStatus}>{inStockAmountStrings?.backorder}</p>
          ) : (
            " "
          )}
          {stockStatus === "instock" && stockQuantity > 0 ? (
            <p className={stockStatus}>
              <SmileOutlined style={{ color: "#0f834d" }} /> {stockQuantity}{" "}
              {inStockAmountStrings?.in_stock}
            </p>
          ) : (
            " "
          )}
        </div>
      )}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  inStockAmountStrings: store.i18n.frontend.in_stock_amount,
});

export default connect(mapStateToProps)(InStockAmount);
