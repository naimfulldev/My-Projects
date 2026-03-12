import { connect } from "react-redux";

const CartSubtotal = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    styles,
    getPropValue,
    properties,
    cartSubtotalStrings,
  } = props;

  if (typeof orderFormData.formCartSubtotal[orderFormId] === "undefined")
    return <></>;

  let emptyCartText =
    getPropValue({
      properties,
      prop: "emptyCartText",
    }) || cartSubtotalStrings?.empty_cart;

  let subtotalSuffix =
    getPropValue({
      properties,
      prop: "subtotalSuffix",
    }) || "";

  const cartSubtotal =
    orderFormData?.formCartSubtotal?.[orderFormId]?.cartSubtotal;

  return (
    <>
      <div
        className="cart-subtotal"
        style={{ ...styles }}
        dangerouslySetInnerHTML={{
          __html: cartSubtotal === "" ? emptyCartText : cartSubtotal,
        }}
      ></div>
    </>
  );
};

const mapStateToProps = (store: any) => ({
  orderFormData: store.orderFormData,
  cartSubtotalStrings: store.i18n.frontend.cart_subtotal,
});

export default connect(mapStateToProps)(CartSubtotal);
