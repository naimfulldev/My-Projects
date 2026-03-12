import { useEffect, useRef, useState } from "react";
import { Button } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { productListActions, orderFormDataActions } from "store/actions/";
import { addProductToCart } from "helpers/addProductToCart";

const { addProductToCartAction } = productListActions;
const { setCartSubtotal } = orderFormDataActions;

const AddToCartButton = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
    useStyleValue,
    addToCartButtonStrings,
    addProductToCartStrings,
  } = props;

  const [stockStatus, setStockStatus] = useState(product.stock_status);

  const addToCartBtnRef = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProducts !== "undefined" &&
        Object.keys(selectedProducts).length > 0 &&
        typeof selectedProduct !== "undefined"
      ) {
        // Stock status
        if (typeof selectedProduct["stockStatus"] !== "undefined") {
          setStockStatus(selectedProduct["stockStatus"]);
        }
      } else {
        setStockStatus(product.stock_status);
      }
    } catch (e) {
      console.log(e);
    }
  }, [
    orderFormData.formSelectedProducts,
    orderFormId,
    product.id,
    product.stock_status,
  ]);

  const onClick = (e: any) => {
    addProductToCart({ ...props, addToCartBtnRef });
  };

  const alignment =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "justifyContent",
    }) || "center";

  let fontSize = useStyleValue(
    getPropValue({
      formStyles,
      item: itemId,
      prop: "fontSize",
    }) || ""
  );

  let width = useStyleValue(
    getPropValue({
      formStyles,
      item: itemId,
      prop: "width",
    }) || ""
  );
  const buttonColor =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "buttonColor",
    }) || "";

  const buttonTextColor =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "buttonTextColor",
    }) || "";
  // CSS
  const alignmentCSS = {
    textAlign:
      alignment === "flex-start"
        ? ("left" as const)
        : alignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const addToCartElementStyle = {
    width,
    fontSize: fontSize === "auto" ? "inherit" : fontSize,
    border: buttonColor,
    background: buttonColor,
    color: buttonTextColor,
    paddingTop:
      getPropValue({
        formStyles,
        item: itemId,
        prop: "paddingTop",
      }) || 0,
    paddingRight:
      getPropValue({
        formStyles,
        item: itemId,
        prop: "paddingRight",
      }) || 0,
    paddingBottom:
      getPropValue({
        formStyles,
        item: itemId,
        prop: "paddingBottom",
      }) || 0,
    paddingLeft:
      getPropValue({
        formStyles,
        item: itemId,
        prop: "paddingLeft",
      }) || 0,
  };

  if (
    addToCartElementStyle.paddingTop === 0 &&
    addToCartElementStyle.paddingRight === 0 &&
    addToCartElementStyle.paddingBottom === 0 &&
    addToCartElementStyle.paddingLeft === 0
  ) {
    delete addToCartElementStyle.paddingTop;
    delete addToCartElementStyle.paddingRight;
    delete addToCartElementStyle.paddingBottom;
    delete addToCartElementStyle.paddingLeft;
  }

  let disabledProp = {};
  if (stockStatus === "outofstock") {
    disabledProp = { disabled: true };
  }

  return (
    <div className="item add-to-cart-button" style={{ ...alignmentCSS }}>
      {product.type !== "variable" ||
      typeof orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
        product.id
      ] !== "undefined" ? (
        <Button
          size="large"
          ref={addToCartBtnRef}
          style={{ ...addToCartElementStyle }}
          type="primary"
          onClick={(e) => onClick(e)}
          {...disabledProp}
        >
          {getPropValue({ formStyles, item: itemId, prop: "buttonText" }) ||
            addToCartButtonStrings?.button_text}
        </Button>
      ) : (
        <em>{addProductToCartStrings?.unavailable}</em>
      )}
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  formStyles: store.orderFormData.formStyles[props.orderFormId],
  addToCartButtonStrings: store.i18n.frontend.add_to_cart_button,
  addProductToCartStrings: store.i18n.frontend.add_product_to_cart,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      addProductToCartAction,
      setCartSubtotal,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(AddToCartButton);
