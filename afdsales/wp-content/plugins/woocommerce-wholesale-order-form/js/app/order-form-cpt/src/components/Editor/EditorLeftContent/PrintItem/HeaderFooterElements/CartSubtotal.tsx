import { useEffect } from "react";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormActions } from "store/actions";

const { setTaxDisplay, setSubtotalPreText, setSubtotalSuffix } =
  orderFormActions;

const CartSubtotal = (props: any) => {
  const {
    properties,
    getPropValue,
    styles,
    orderForm,
    actions,
    cartSubtotalStrings,
  } = props;
  const { setTaxDisplay, setSubtotalSuffix, setSubtotalPreText } = actions;

  const { subtotal_pretext, empty_cart_text } = cartSubtotalStrings;

  let subtotalPreText =
    getPropValue({
      properties,
      prop: "preText",
    }) || subtotal_pretext;

  let emptyCartText =
    getPropValue({
      properties,
      prop: "emptyCartText",
    }) || empty_cart_text;

  let subtotalSuffix =
    getPropValue({
      properties,
      prop: "subtotalSuffix",
    }) || "";

  let taxDisplay = getPropValue({
    properties,
    prop: "taxDisplay",
  });

  useEffect(() => {
    setTaxDisplay(taxDisplay);
  }, [taxDisplay]);

  useEffect(() => {
    setSubtotalPreText(subtotalPreText);
  }, [subtotalPreText]);

  useEffect(() => {
    setSubtotalSuffix(subtotalSuffix);
  }, [subtotalSuffix]);

  return (
    <div
      className="cart-subtotal"
      style={{ ...styles }}
      dangerouslySetInnerHTML={{
        __html:
          orderForm.cartSubtotal === ""
            ? emptyCartText
            : orderForm.cartSubtotal,
      }}
    ></div>
  );
};

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    { setTaxDisplay, setSubtotalPreText, setSubtotalSuffix },
    dispatch
  ),
});

const mapStateToProps = (store: any, props: any) => ({
  cartSubtotalStrings: store.i18n.backend.header_footer_elements.cart_subtotal,
});

export default connect(mapStateToProps, mapDispatchToProps)(CartSubtotal);
