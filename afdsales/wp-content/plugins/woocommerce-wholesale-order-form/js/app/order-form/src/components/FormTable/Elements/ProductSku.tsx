import { useState, useEffect } from "react";
import { connect } from "react-redux";

const ProductSku = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
  } = props;

  const [productSku, setProductSku] = useState(product.sku);

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

            if (typeof variationData !== "undefined")
              setProductSku(variationData.sku);
            else {
              setProductSku(product.sku);
            }
          }
        }
      } else {
        setProductSku(product.sku);
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
      className="item product-sku"
      style={{ minWidth: "100px", ...alignmentCSS }}
    >
      {productSku || " "}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
});

export default connect(mapStateToProps)(ProductSku);
