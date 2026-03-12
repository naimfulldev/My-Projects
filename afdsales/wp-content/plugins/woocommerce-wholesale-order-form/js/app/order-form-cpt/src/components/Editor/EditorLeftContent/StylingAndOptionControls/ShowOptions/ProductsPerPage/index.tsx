import { useState, useEffect } from "react";
import { InputNumber } from "antd";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import {
  paginationActions,
  productActions,
  orderFormActions,
} from "store/actions";

const { setOrderFormSettingsData } = orderFormActions;
const { setPaginationState } = paginationActions;
const { fetchProducts } = productActions;

const ProductsPerPage = (props: any) => {
  const {
    products,
    settingsData,
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    actions,
    productsPerPageStrings,
  } = props;

  const { label } = productsPerPageStrings;

  const { setPaginationState, setOrderFormSettingsData } = actions;
  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "productsPerPage",
      extra: "",
    }) || 10
  );

  useEffect(() => {
    if (
      typeof settingsData !== "undefined" &&
      typeof settingsData.products_per_page !== "undefined"
    ) {
      setValue(settingsData.products_per_page);
    }
  }, [styling.item]);

  useEffect(() => {
    // If value is changed we update the
    fetchProducts({
      per_page: value,
    });
  }, [value]);

  useEffect(() => {
    // If products is updated we update the products per page
    setPaginationState({
      per_page: value,
    });
  }, [products]);

  useEffect(() => {
    // Save this in the settings
    setOrderFormSettingsData({
      products_per_page: value,
    });
  }, [value]);

  return (
    <div className="products-per-page">
      <label htmlFor="products-per-page">{label}:</label>
      <InputNumber
        value={value}
        onChange={(val: any) => {
          if (val === null || val === "" || val === 0) return;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              productsPerPage: val,
            },
          });
          setValue(val);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  settingsData: store.orderForm.settingsData,
  products: store.products,
  productsPerPageStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .products_per_page,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setPaginationState,
      fetchProducts,
      setOrderFormSettingsData,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductsPerPage);
