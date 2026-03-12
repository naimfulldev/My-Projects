import { useState, useEffect } from "react";
import { Button, InputNumber, Popover } from "antd";
import quantityRestrictionPopover from "helpers/quantityRestrictionPopover";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormDataActions } from "store/actions/";
import { getProductMinQtyAndStep } from "helpers/getProductMinQtyAndStep";
const { setSelectedProductsToAddToCart } = orderFormDataActions;

declare var WWOF_Frontend_Options: any;

const QuantityInput = (props: any) => {
  const {
    showModal,
    orderFormData,
    orderFormId,
    product,
    actions,
    getPropValue,
    formStyles,
    itemId,
    quantityInputStrings,
  } = props;

  const { setSelectedProductsToAddToCart } = actions;
  const [quantity, setQuantity] = useState<any>(1);
  const [quantityStep, setQuantityStep] = useState(1);
  const [, setMinQuantity] = useState(1);
  const [stockStatus, setStockStatus] = useState(product.stock_status);

  // Popover
  const [popoverVisibility, setPopoverVisibility] = useState(false);
  const [popoverContent, setPopoverContent] = useState("");

  /* Styles and Options */
  const alignment =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "justifyContent",
    }) ?? "center";

  const alignmentCSS = {
    textAlign:
      alignment === "flex-start"
        ? ("left" as const)
        : alignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const outOfStockText =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "outOfStockText",
    }) ?? quantityInputStrings?.out_of_stock;

  const decimalQuantity =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "decimalQuantity",
    }) ?? false;

  /* End Options and Settings */

  // Close popover when modal is open
  useEffect(() => {
    try {
      if (showModal === true) {
        setPopoverVisibility(false);
      }
    } catch (e) {
      console.log(e);
    }
  }, [showModal]);

  // When navigating to next page set quantity based on the previously set quantity.
  useEffect(() => {
    try {
      const selectedProduct =
        orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id];

      if (typeof selectedProduct?.quantity !== "undefined") {
        setQuantity(selectedProduct.quantity);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formPagination[orderFormId]]);

  // If selected products data is changed then update quantity box
  // Only for wholesale variable variations with min and step
  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProducts !== "undefined" &&
        typeof selectedProduct !== "undefined"
      ) {
        // Set Stock Status
        if (typeof selectedProduct?.["stockStatus"] !== "undefined")
          setStockStatus(selectedProduct["stockStatus"]);

        if (
          product.type === "variable" &&
          typeof selectedProduct?.["variationSelected"] !== "undefined"
        ) {
          // If variation changes, set the qty to be the min order qty
          let formSelectedProducts = selectedProduct;

          delete formSelectedProducts.variationSelected;

          setSelectedProductsToAddToCart({
            [orderFormId]: {
              ...selectedProducts,
              [product.id]: {
                ...formSelectedProducts,
              },
            },
          });

          // Min Quantity and Step
          const { minOrderQty, orderQtyStep } = getProductMinQtyAndStep({
            ...selectedProduct,
          });
          setMinQuantity(minOrderQty);
          setQuantity(minOrderQty);
          setQuantityStep(orderQtyStep);
        } else {
          setQuantities();
        }
      } else {
        setQuantities();
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  const onQuantityChange = async (quantity: number) => {
    try {
      let popover = quantityRestrictionPopover({ ...props, quantity });

      if (typeof popover !== "undefined") {
        setPopoverVisibility(popover.visible);
        setPopoverContent(popover.content);
      }

      let inputQuantity = quantity;

      // If Decimal Quantity is not enabled.
      // By default Decimal quantity is not allowed.
      if (!decimalQuantity) {
        inputQuantity = Math.round(quantity);
      }

      setQuantity(inputQuantity);

      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProducts !== "undefined" &&
        typeof selectedProduct !== "undefined"
      ) {
        // If we have this data in redux state then update quantity
        setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...selectedProducts,
            [product.id]: {
              ...selectedProduct,
              quantity: inputQuantity,
            },
          },
        });
      } else {
        // Else add these data
        setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...selectedProducts,
            [product.id]: {
              productID: product.id,
              productTitle: product.name,
              productType: product.type,
              quantity: inputQuantity,
              stockStatus: product.stock_status,
              wholesaleData:
                typeof product.wholesale_data !== "undefined"
                  ? product.wholesale_data
                  : [],
            },
          },
        });
      }
    } catch (err) {
      console.log(err);
    }
  };

  const setQuantities = () => {
    if (
      typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
      WWOF_Frontend_Options.wholesale_role !== ""
    ) {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProducts !== "undefined" &&
        typeof selectedProduct !== "undefined"
      ) {
        // Min Quantity and Step
        const { minOrderQty, orderQtyStep } = getProductMinQtyAndStep({
          ...selectedProduct,
        });

        setQuantity(selectedProduct?.quantity ?? minOrderQty);
        setQuantityStep(orderQtyStep);
      } else {
        // Min Quantity and Step
        const { minOrderQty, orderQtyStep } = getProductMinQtyAndStep({
          productType: product.type,
          wholesaleData:
            typeof product.wholesale_data !== "undefined"
              ? product.wholesale_data
              : [],
          variationData: [],
          variationID: 0,
        });

        setQuantity(minOrderQty);
        setQuantityStep(orderQtyStep);
      }
    } else {
      let selectedProduct =
        orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id];

      if (
        typeof selectedProduct?.quantity !== "undefined" &&
        selectedProduct.quantity !== quantity
      ) {
        setQuantity(selectedProduct.quantity);
      }
    }
  };

  useEffect(() => {
    try {
      setQuantities();
    } catch (e) {
      console.log(e);
    }
  }, []);

  return (
    <div className="item quantity-input" style={{ ...alignmentCSS }}>
      {stockStatus === "outofstock" ? (
        <> {outOfStockText} </>
      ) : (
        <>
          {product.type !== "variable" ||
          typeof orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
            product.id
          ] !== "undefined" ? (
            <Popover
              content={
                <>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: popoverContent,
                    }}
                  />
                  <Button onClick={() => setPopoverVisibility(false)}>
                    {quantityInputStrings?.close_popover}
                  </Button>
                </>
              }
              title={quantityInputStrings?.quantity_restriction}
              visible={popoverVisibility}
            >
              <InputNumber
                size="large"
                min={0}
                step={quantityStep}
                defaultValue={1}
                value={quantity}
                onChange={(quantity: any) => onQuantityChange(quantity)}
              />
            </Popover>
          ) : (
            <></>
          )}
        </>
      )}
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  showModal: store.orderForm.showModal,
  orderFormData: store.orderFormData,
  formStyles: store.orderFormData.formStyles[props.orderFormId],
  quantityInputStrings: store.i18n.frontend.quantity_input,
  qtyRestrictionStrings: store.i18n.frontend.quantity_restriction_popover,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setSelectedProductsToAddToCart,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(QuantityInput);
