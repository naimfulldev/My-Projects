import React, { useState, useEffect } from "react";
import { connect } from "react-redux";
import { bindActionCreators, Dispatch } from "redux";
import { orderFormActions } from "store/actions";
import { htmlDecode } from "helpers/htmlDecode";
import VariationAttributes from "./shared/VariationAttributes";

const { setShowModal } = orderFormActions;

const ProductName = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
    actions,
  } = props;

  const { setShowModal } = actions;

  const [productName, setProductName] = useState(product.name);

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProducts !== "undefined" &&
        Object.keys(selectedProducts).length > 0
      ) {
        const variationID = selectedProduct?.["variationID"];

        const variations =
          orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
            product.id
          ];

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

            if (
              typeof variationData !== "undefined" &&
              typeof variationData.name !== "undefined"
            )
              setProductName(variationData.name);
          }
        }
      } else {
        setProductName(product.name);
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

  const onClickAction =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "onClick",
    }) || "show-product-details";

  return (
    <div
      className="item product-name"
      style={{ minWidth: "200px", ...alignmentCSS }}
    >
      <a
        href="#"
        className="product-name link"
        style={{ padding: "0px", fontWeight: 400, ...alignmentCSS }}
        onClick={(e: React.SyntheticEvent<HTMLAnchorElement>) => {
          e.preventDefault();
          if (onClickAction === "show-product-details") {
            setShowModal({
              showModal: true,
              modalProps: {
                orderFormId,
                product,
                onClickAction,
              },
            });
          } else {
            window.location.href = product.permalink;
          }
        }}
      >
        {htmlDecode(productName)}
      </a>
      <VariationAttributes product={product} />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({ setShowModal }, dispatch),
});
export default connect(mapStateToProps, mapDispatchToProps)(ProductName);
