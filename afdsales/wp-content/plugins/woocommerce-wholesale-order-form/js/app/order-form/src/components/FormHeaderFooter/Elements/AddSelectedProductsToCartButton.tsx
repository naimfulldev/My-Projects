import { useEffect, useState } from "react";
import { Button, notification, List } from "antd";
import { ShoppingCartOutlined } from "@ant-design/icons";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { productListActions, orderFormDataActions } from "store/actions/";
import { getProductMinQtyAndStep } from "helpers/getProductMinQtyAndStep";

const { addProductsToCartAction } = productListActions;
const { setCartSubtotal, setSelectedProductsToAddToCart, setPageSelectedAll } =
  orderFormDataActions;

// Get option from table
const getOption = (props: any) => {
  const { formStyles, item, prop } = props;
  const propValue = formStyles?.[item]?.["props"]?.[prop];

  return typeof propValue !== "undefined" && propValue !== ""
    ? propValue
    : null;
};

const AddSelectedProductsToCartButton = (props: any) => {
  const {
    getPropValue,
    properties,
    orderFormId,
    styles,
    actions,
    formStyles,
    orderForm,
    orderFormData,
    addSelectedProductsToCartButtonStrings,
  } = props;

  const {
    addProductsToCartAction,
    setCartSubtotal,
    setSelectedProductsToAddToCart,
    setPageSelectedAll,
  } = actions;

  const [selectedInstockProducts, setSelectedInstockProducts] = useState<any>(
    {}
  );

  const buttonText =
    getPropValue({ properties, prop: "buttonText" }) ??
    addSelectedProductsToCartButtonStrings?.button_text;

  const addToCartSelectedProducts = () => {
    let selectedProducts = selectedInstockProducts;
    let selectedProductsClone: any = Object.assign({}, selectedProducts);
    let selectedVariableProducts: any = {}; // Retain variable products
    let qtyRestrictionErr: any = [];
    let failedToAddProducts: any = [];

    let quantityRestriction = getOption({
      formStyles,
      item: "quantity-input",
      prop: "quantityRestriction",
    });

    if (quantityRestriction === undefined || quantityRestriction === null) {
      quantityRestriction = true;
    }

    Object.keys(selectedProducts).map((id: any) => {
      const { minOrderQty, orderQtyStep } = getProductMinQtyAndStep({
        ...selectedProducts[id],
      });

      let quantity = selectedProducts[id].quantity;
      let multiplier: any = (quantity - minOrderQty) / orderQtyStep;
      multiplier = parseInt(multiplier, 10);
      let nearestLow = minOrderQty + orderQtyStep * multiplier;
      let nearestHigh = minOrderQty + orderQtyStep * (multiplier + 1);
      let excessQty = quantity - minOrderQty;

      if (quantity === 0) {
        qtyRestrictionErr.push({
          error_message: addSelectedProductsToCartButtonStrings?.zero_qty_error,
          product_id: id,
          quantity: quantity,
        });

        failedToAddProducts[orderFormId] = {
          ...failedToAddProducts[orderFormId],
          [id]: {
            ...selectedProductsClone[id],
          },
        };

        delete selectedProductsClone[id];
      } else if (quantityRestriction) {
        // Quantity restriction rule
        if (minOrderQty > 1 && quantity < minOrderQty) {
          qtyRestrictionErr.push({
            error_message: `${addSelectedProductsToCartButtonStrings?.minimum_error} <b>${minOrderQty}</b>.`,
            product_id: id,
            quantity: quantity,
          });

          failedToAddProducts[orderFormId] = {
            ...failedToAddProducts[orderFormId],
            [id]: {
              ...selectedProductsClone[id],
            },
          };

          delete selectedProductsClone[id];
        } else if (
          minOrderQty > 1 &&
          orderQtyStep > 1 &&
          excessQty % orderQtyStep !== 0
        ) {
          qtyRestrictionErr.push({
            error_message: `${addSelectedProductsToCartButtonStrings?.step_error} <b>${nearestLow}</b> ${addSelectedProductsToCartButtonStrings?.and} <b>${nearestHigh}</b>.`,
            product_id: id,
            quantity: quantity,
          });

          failedToAddProducts[orderFormId] = {
            ...failedToAddProducts[orderFormId],
            [id]: {
              ...selectedProductsClone[id],
            },
          };

          delete selectedProductsClone[id];
        }
      }

      // No selected variation
      if (
        typeof selectedProductsClone[id] !== "undefined" &&
        selectedProductsClone[id].productType === "variable"
      ) {
        if (
          (typeof selectedProducts[id].variationID !== "undefined" &&
            selectedProducts[id].variationID <= 0) ||
          typeof selectedProducts[id].variationID === "undefined"
        ) {
          qtyRestrictionErr.push({
            error_message:
              addSelectedProductsToCartButtonStrings?.no_variations_selected,
            product_id: id,
            quantity: quantity,
          });

          failedToAddProducts[orderFormId] = {
            ...failedToAddProducts[orderFormId],
            [id]: {
              ...selectedProductsClone[id],
            },
          };

          delete selectedProductsClone[id];
        }
      }
    });

    // Get all variable products
    // Retain the variable products
    // Set selected to false
    Object.keys(orderFormData.formSelectedProducts[orderFormId]).forEach(
      (pid: any) => {
        if (
          typeof orderFormData.formSelectedProducts[orderFormId][pid]
            .productType !== "undefined" &&
          orderFormData.formSelectedProducts[orderFormId][pid].productType ===
            "variable"
        ) {
          selectedVariableProducts = {
            ...selectedVariableProducts,
            [pid]: {
              ...orderFormData.formSelectedProducts[orderFormId][pid],
              selected: false,
            },
          };
        }
      }
    );

    // Remove wholesale data, not needed for add to cart
    Object.keys(selectedProductsClone).map((id: any) => {
      if (typeof selectedProductsClone[id].wholesaleData) {
        delete selectedProductsClone[id].wholesaleData;
      }
    });

    addProductsToCartAction({
      products: selectedProductsClone,
      form_settings: orderFormData.formSettings[orderFormId],
      successCB: (args: any) => {
        let added: any = [];
        const successfully_added = args.data.successfully_added;

        Object.keys(successfully_added).forEach((productId) => {
          const product = selectedProducts[productId] || {};

          if (Object.keys(product).length > 0) {
            if (["simple", "variation"].includes(product.productType)) {
              let name = selectedProducts[productId].variationName || "";

              added.push(
                <div
                  dangerouslySetInnerHTML={{
                    __html: `<b>${product.productTitle}</b> x ${successfully_added[productId]} <br/>${name}`,
                  }}
                />
              );
            }
          } else {
            Object.keys(selectedProducts).map((id: any, test: any) => {
              if (
                typeof selectedProducts[id].variationID !== "undefined" &&
                parseInt(selectedProducts[id].variationID) ===
                  parseInt(productId)
              ) {
                let name = selectedProducts[id].variationName || "";

                added.push(
                  <div
                    dangerouslySetInnerHTML={{
                      __html: `<b>${selectedProducts[id].productTitle}</b> x ${successfully_added[productId]}<br/>${name}`,
                    }}
                  />
                );
              }
            });
          }
        });

        let failed: any = [];
        let failed_to_add = args.data.failed_to_add;
        failed_to_add = failed_to_add.concat(qtyRestrictionErr);
        failed_to_add.reverse();

        failed_to_add.forEach((data: any, index: number) => {
          const product = selectedProducts[data.product_id] || {};

          if (product)
            failed.push(
              <div
                dangerouslySetInnerHTML={{
                  __html: `<b>${product.productTitle}</b> x ${data.quantity}<br/>${data.error_message}`,
                }}
              />
            );
        });

        if (added.length > 0) {
          notification["success"]({
            message: `${addSelectedProductsToCartButtonStrings?.successfully_added}:`,
            description: (
              <div>
                <List
                  size="small"
                  bordered
                  dataSource={added}
                  renderItem={(item: any) => <List.Item>{item}</List.Item>}
                />
                <a href={orderForm.cartURL} target="_blank">
                  <Button style={{ marginTop: "10px" }}>
                    View Cart
                    <ShoppingCartOutlined />
                  </Button>
                </a>
              </div>
            ),
            duration: 10,
          });
        }

        if (failed.length > 0) {
          notification["error"]({
            message: `${addSelectedProductsToCartButtonStrings?.add_to_cart_failed}:`,
            description: (
              <List
                size="small"
                bordered
                dataSource={failed}
                renderItem={(item: any) => <List.Item>{item}</List.Item>}
              />
            ),
            duration: 10,
          });
        }

        // Update cart total by triggering the added_to_cart custom event of wc.
        const fragments: any = args.data.fragments;
        const cart_hash: any = args.data.cart_hash;

        const event = new CustomEvent("added_to_cart", {
          detail: { fragments: fragments, cart_hash: cart_hash },
        });
        document.body.dispatchEvent(event);

        // Update subtotal below the order form.
        setCartSubtotal({
          [orderFormId]: {
            cartSubtotal: args.data.cart_subtotal_markup,
          },
        });

        // Successfuly added products are removed from selected state
        // Failed to add products are retain in selected state (so that they can re-correct and select proper variation)
        setSelectedProductsToAddToCart({
          ...orderFormData.formSelectedProducts,
          [orderFormId]: {
            ...selectedVariableProducts,
            ...failedToAddProducts[orderFormId],
          },
        });

        // Clear Select All for the current page
        if (
          typeof orderFormData.formPagination[orderFormId] !== "undefined" &&
          typeof orderFormData.formPagination[orderFormId]["selectedAll"] !==
            "undefined"
        ) {
          let selectedAll =
            orderFormData.formPagination[orderFormId]["selectedAll"];

          Object.entries(selectedAll).map((data: any) => {
            setPageSelectedAll({
              orderFormId,
              data: {
                [data[0]]: false,
              },
            });
          });
        }
      },
      failCB: (args: any) => {
        notification["error"]({
          message: `${addSelectedProductsToCartButtonStrings?.add_to_cart_failed}:`,
          description: "error",
        });
      },
    });
  };

  let buttonColor = getPropValue({
    properties,
    prop: "buttonColor",
  });

  let buttonTextColor = getPropValue({
    properties,
    prop: "buttonTextColor",
  });

  const addSelectedToCartElementStyle = {
    background: buttonColor,
    color: buttonTextColor,
    border: buttonColor,
  };

  useEffect(() => {
    if (
      typeof orderFormData.formSelectedProducts[orderFormId] !== "undefined"
    ) {
      if (
        Object.keys(orderFormData.formSelectedProducts[orderFormId]).length > 0
      ) {
        let updatedList = {};
        Object.keys(orderFormData.formSelectedProducts[orderFormId]).forEach(
          (pid: any) => {
            const product =
              orderFormData?.formSelectedProducts?.[orderFormId]?.[pid];
            const stockStatus = product?.stockStatus;
            const selected = product?.selected;

            if (
              typeof stockStatus !== "undefined" &&
              stockStatus !== "outofstock" &&
              typeof selected !== "undefined" &&
              selected === true
            ) {
              updatedList = {
                ...updatedList,
                [pid]: {
                  ...product,
                },
              };
            }
          }
        );

        // Remove outofstock products
        setSelectedInstockProducts(updatedList);
      } else {
        // Set selected products as empty
        setSelectedInstockProducts({});
      }
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  if (
    typeof orderFormData.formSelectedProducts[orderFormId] !== "undefined" &&
    Object.keys(selectedInstockProducts).length > 0 &&
    orderFormData.formTable[orderFormId]["itemIds"].includes(
      "add-to-cart-checkbox"
    )
  )
    return (
      <Button
        size="large"
        className="add-selected-to-cart-button"
        type="primary"
        style={{ ...styles, ...addSelectedToCartElementStyle }}
        onClick={() => addToCartSelectedProducts()}
      >
        {buttonText}
      </Button>
    );
  else
    return (
      <Button
        size="large"
        className="add-selected-to-cart-button"
        type="primary"
        style={{ ...styles, ...addSelectedToCartElementStyle }}
        disabled
      >
        {buttonText}
      </Button>
    );
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  formStyles: store.orderFormData.formStyles[props.orderFormId],
  addSelectedProductsToCartButtonStrings:
    store.i18n.frontend.add_selected_products_to_cart_button,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      addProductsToCartAction,
      setCartSubtotal,
      setSelectedProductsToAddToCart,
      setPageSelectedAll,
    },
    dispatch
  ),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(AddSelectedProductsToCartButton);
