import { useEffect, useState } from "react";
import { substr } from "stringz";
import { connect } from "react-redux";

const ShortDescription = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
  } = props;

  const [productDescription, setProductDescription] = useState(
    product.short_description
  );

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
          const variationID = selectedProduct["variationID"];
          const variations =
            orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
              product.id
            ];

          if (typeof variations !== "undefined") {
            const variationData = variations.find((variation: any) => {
              return variation.id === variationID;
            });

            if (typeof variationData !== "undefined")
              setProductDescription(variationData.description);
          }
        }
      } else {
        setProductDescription(product.short_description);
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

  let maxCharacters = getPropValue({
    formStyles,
    item: itemId,
    prop: "maxCharacters",
  });

  if (maxCharacters === undefined || maxCharacters === null) {
    maxCharacters = 200;
  }

  let limiter: string = "";
  if (
    maxCharacters !== "" &&
    productDescription.length > parseInt(maxCharacters)
  ) {
    limiter = "...";
  }

  return (
    <div
      className="item product-description"
      style={{ minWidth: "250px", ...alignmentCSS }}
      dangerouslySetInnerHTML={{
        __html:
          productDescription && maxCharacters !== ""
            ? substr(productDescription, 0, parseInt(maxCharacters)) + limiter
            : productDescription === ""
            ? "&nbsp;"
            : " ",
      }}
    ></div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
});

export default connect(mapStateToProps)(ShortDescription);
