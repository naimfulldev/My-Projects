import { useEffect, useState } from "react";
import { Select } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormActions, orderFormDataActions } from "store/actions";

const { loadMoreVariations } = orderFormActions;
const { setSelectedProductsToAddToCart } = orderFormDataActions;

const { Option } = Select;

declare var WWOF_Frontend_Options: any;

const ComboVariationsDropdown = (props: any) => {
  const {
    product,
    variations,
    orderFormData,
    orderFormId,
    actions,
    setSelectedVariation,
    comboStrings,
  } = props;
  const { setSelectedProductsToAddToCart, loadMoreVariations } = actions;

  const [selectProps, setSelectProps] = useState({});
  const [variationOptions, setVariationOptions] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    try {
      if (typeof variations !== "undefined") {
        // Actual total variations. Not included variations will be lazy loaded.
        const totalVariations =
          orderFormData.formProducts[orderFormId].lazy_load_variations_data[
            product.id
          ].total_variations;

        const options = variations.map((variation: any, i: number) => {
          const totalAttr = variation.attributes.length;
          const name = variation.attributes.map(
            (attribute: any, key: number) => {
              return (
                <span key={key}>
                  {`${
                    key === 0
                      ? "(" + (i + 1) + "/" + totalVariations + ") "
                      : ""
                  }`}
                  {`${attribute.name}: ${attribute.option}`}
                  {`${totalAttr !== key + 1 ? ", " : ""}`}
                </span>
              );
            }
          );
          return (
            <Option
              key={variation.id}
              value={variation.id}
              className="combo-variation"
            >
              {name}
            </Option>
          );
        });

        setVariationOptions(options);
      }
    } catch (e) {
      console.log(e);
    }
  }, [variations]);

  // setSelectedVariation function use used in modal
  useEffect(() => {
    try {
      const selectedProduct =
        orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id];

      if (
        typeof selectedProduct !== "undefined" &&
        typeof selectedProduct?.variationID !== "undefined" &&
        typeof setSelectedVariation === "function"
      ) {
        setSelectedVariation(selectedProduct.variationID);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formPagination[orderFormId]]);

  const onVariationChange = (variationID: number) => {
    const selectedProducts = orderFormData?.formSelectedProducts?.[orderFormId];
    const selectedProduct = selectedProducts?.[product.id];

    if (typeof variationID !== "undefined") {
      let quantity = 1;
      let stockStatus = product.stock_status;
      let wholesaleData: any = [];
      let variationData: any = [];

      // Set selected variation name
      const variation = variations.find((variation: any) => {
        return variation.id === variationID;
      });
      const name = variation.attributes.map((attributes: any) => {
        return attributes.name + " : " + attributes.option;
      });

      // Stock status
      if (typeof variation !== "undefined")
        stockStatus = variation.stock_status;

      // Wholesale Data
      if (
        typeof variation !== "undefined" &&
        typeof variation.wholesale_data !== "undefined"
      )
        variationData = variation.wholesale_data;
      if (typeof product.wholesale_data !== "undefined")
        wholesaleData = product.wholesale_data;

      // Set quantity if Wholesale Min Order Quantity is set
      if (
        typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
        typeof product.wholesale_data !== "undefined"
      ) {
        if (
          typeof variation.wholesale_data.wholesale_minimum_order_quantity !==
            "undefined" &&
          typeof variation.wholesale_data.wholesale_minimum_order_quantity[
            WWOF_Frontend_Options.wholesale_role
          ] !== "undefined"
        ) {
          // Minimum Order Quantity is set in Simple/Variation
          quantity =
            variation.wholesale_data.wholesale_minimum_order_quantity[
              WWOF_Frontend_Options.wholesale_role
            ];
        } else if (
          typeof product.wholesale_data
            .variable_level_wholesale_minimum_order_quantity !== "undefined" &&
          typeof product.wholesale_data
            .variable_level_wholesale_minimum_order_quantity[
            WWOF_Frontend_Options.wholesale_role
          ] !== "undefined"
        ) {
          // Minimum Order Quantity is set in Variable
          quantity =
            product.wholesale_data
              .variable_level_wholesale_minimum_order_quantity[
              WWOF_Frontend_Options.wholesale_role
            ];
        }
      }

      // Add selected variation into the state selected products
      if (
        typeof selectedProducts !== "undefined" &&
        typeof selectedProduct !== "undefined"
      ) {
        setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...selectedProducts,
            [product.id]: {
              ...selectedProduct,
              productID: product.id,
              variationID,
              productTitle: product.name,
              productType: product.type,
              name: name.join("<br/>"),
              quantity,
              variationSelected: true,
              stockStatus,
              wholesaleData,
              variationData,
            },
          },
        });
      } else {
        setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...selectedProducts,
            [product.id]: {
              productID: product.id,
              variationID,
              productTitle: product.name,
              productType: product.type,
              name: name.join("<br/>"),
              quantity,
              variationSelected: true,
              stockStatus,
              wholesaleData,
              variationData,
            },
          },
        });
      }
      // State From ProductModal
      if (typeof setSelectedVariation === "function")
        setSelectedVariation(variationID);

      // Selected Variation
      setSelectProps({
        value: variationID,
      });
    } else {
      // Remove the product from selected products
      const formSelectedProductsCopy = selectedProducts;

      if (typeof selectedProduct !== "undefined") {
        // delete formSelectedProducts[product.id];
        formSelectedProductsCopy[product.id]["variationID"] = 0;

        setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...selectedProducts,
            ...formSelectedProductsCopy,
          },
        });
        // State From ProductModal
        if (typeof setSelectedVariation === "function") setSelectedVariation(0);
      }

      // Selected Variation Empty
      setSelectProps({});
    }
  };

  useEffect(() => {
    const selectedProducts = orderFormData?.formSelectedProducts?.[orderFormId];
    const selectedProduct = selectedProducts?.[product.id];

    if (
      typeof selectedProduct?.variationID !== "undefined" &&
      selectedProduct.variationID > 0
    ) {
      setSelectProps({
        value: selectedProduct.variationID,
      });
    } else {
      setSelectProps({});
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  useEffect(() => {
    const selectedProducts = orderFormData?.formSelectedProducts?.[orderFormId];
    const selectedProduct = selectedProducts?.[product.id];

    if (
      typeof selectedProduct?.defaultVariationID !== "undefined" &&
      selectedProduct.defaultVariationID > 0
    ) {
      setSelectProps({
        value: selectedProduct.defaultVariationID,
      });
    } else if (
      typeof selectedProduct?.variationID !== "undefined" &&
      selectedProduct.variationID > 0
    ) {
      setSelectProps({
        value: selectedProduct.variationID,
      });
    } else {
      setSelectProps({});
    }
  }, []);

  const lazyLoading = (event: any) => {
    var target = event.target;

    if (
      !loading &&
      target.scrollTop + target.offsetHeight === target.scrollHeight
    ) {
      try {
        let nextPage =
          orderFormData.formProducts[orderFormId].lazy_load_variations_data[
            product.id
          ].current_page + 1;

        // Make sure not to go over the limit
        if (
          nextPage <=
          orderFormData.formProducts[orderFormId].lazy_load_variations_data[
            product.id
          ].total_page
        ) {
          setLoading(true);
          loadMoreVariations({
            orderFormId,
            orderFormData,
            wholesale_role: WWOF_Frontend_Options.wholesale_role,
            product_id: product.id,
            current_page:
              orderFormData.formProducts[orderFormId].lazy_load_variations_data[
                product.id
              ].current_page + 1,
            successCB: () => {
              setLoading(false);
            },
            failCB: () => {
              setLoading(false);
            },
          });
        }
      } catch (e) {
        console.log(e);
      }
    }
  };

  if (!variationOptions) return <></>;
  else
    return (
      <Select
        size="large"
        placeholder={comboStrings?.placeholder}
        style={{ textAlign: "left" }}
        filterOption={false}
        notFoundContent={comboStrings?.no_results}
        allowClear={true}
        {...selectProps}
        onChange={(variationId: any) => onVariationChange(variationId)}
        onPopupScroll={lazyLoading}
      >
        {!loading
          ? variationOptions
          : [
              variationOptions,
              <Option value="loading" key="loading">
                {comboStrings?.loading_text}
              </Option>,
            ]}
      </Select>
    );
};

const mapStateToProps = (store: any, props: any) => ({
  comboStrings: store.i18n.frontend.combo,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setSelectedProductsToAddToCart,
      loadMoreVariations,
    },
    dispatch
  ),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(ComboVariationsDropdown);
