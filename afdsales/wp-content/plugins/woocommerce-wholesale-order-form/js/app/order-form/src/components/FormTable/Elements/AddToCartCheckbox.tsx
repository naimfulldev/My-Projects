import { useState, useEffect } from "react";
import { Checkbox } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormDataActions } from "store/actions/";

const { updateSelectedProducts } = orderFormDataActions;

const AddToCartButton = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    selectedAll,
    somethingChanged,
    product,
    actions,
  } = props;

  const { updateSelectedProducts } = actions;
  const [selected, setSelected] = useState(false);

  const [stockStatus, setStockStatus] = useState(product.stock_status);

  const onChange = (e: any) => {
    setSelected(e.target.checked);

    updateSelectedProducts({
      selected: e.target.checked,
      orderFormData,
      orderFormId,
      product,
    });
  };

  useEffect(() => {
    try {
      if (somethingChanged) setSelected(selectedAll);
    } catch (e) {
      console.log(e);
    }
  }, [selectedAll, somethingChanged]);

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (typeof selectedProducts !== "undefined") {
        if (selectedProduct?.selected === true) {
          // selected
          setSelected(true);

          // Stock status
          if (typeof selectedProduct?.["stockStatus"] !== "undefined") {
            if (selectedProduct?.["stockStatus"] === "outofstock")
              setSelected(false);

            setStockStatus(selectedProduct?.["stockStatus"]);
          }
        } else setSelected(false);
      } else {
        setSelected(false);
      }
    } catch (e) {
      console.log(e);
    }
  }, [
    orderFormData.formPagination[orderFormId],
    orderFormData.formSelectedProducts[orderFormId],
  ]);

  let disabledProp = {};
  if (stockStatus === "outofstock") {
    disabledProp = { disabled: true, checked: false };
  }

  return (
    <div
      className="item add-to-cart-checkbox"
      style={{ minWidth: "100px", textAlign: "center" }}
    >
      <Checkbox
        checked={selected}
        onChange={(e) => onChange(e)}
        {...disabledProp}
      />
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  formStyles: store.orderFormData.formStyles[props.orderFormId],
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      updateSelectedProducts,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(AddToCartButton);
