import { useState, useEffect } from "react";
import { InputNumber } from "antd";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormActions } from "store/actions";

const { setOrderFormSettingsData } = orderFormActions;
const ProductsPerPage = (props: any) => {
  const { orderForm, actions } = props;
  const { setOrderFormSettingsData } = actions;

  const [value, setValue] = useState(0);

  useEffect(() => {
    const products_per_page = orderForm?.settingsData?.products_per_page;
    if (typeof products_per_page !== "undefined") {
      setValue(products_per_page);
    }
  }, [orderForm.settingsData]);

  useEffect(() => {
    if (value === 0) return;

    const products_per_page = orderForm?.settingsData?.products_per_page;
    if (
      typeof products_per_page !== "undefined" &&
      products_per_page !== value
    ) {
      setOrderFormSettingsData({
        products_per_page: value,
      });
    }
  }, [setOrderFormSettingsData, value]);

  const lazy_loading = orderForm?.settingsData?.lazy_loading;

  if (typeof lazy_loading !== "undefined" && lazy_loading === true) {
    return (
      <div className="products-per-page" style={{ marginLeft: "24px" }}>
        <label htmlFor="products-per-page">
          <b>Products Per Page:</b>
        </label>{" "}
        <InputNumber
          value={value}
          onChange={(val: any) => {
            if (val === null || val === "" || val === 0) return;
            setValue(val);
          }}
        />
      </div>
    );
  } else return <></>;
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({ setOrderFormSettingsData }, dispatch),
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductsPerPage);
