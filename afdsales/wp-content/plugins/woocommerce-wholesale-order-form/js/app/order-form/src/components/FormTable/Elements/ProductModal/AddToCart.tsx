import { useState, useEffect, useRef, useCallback } from "react";
import { InputNumber, Button, Popover } from "antd";
import { FrownOutlined, SmileOutlined } from "@ant-design/icons";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { productListActions, orderFormDataActions } from "store/actions";
import { addProductToCart } from "helpers/addProductToCart";
import quantityRestrictionPopover from "helpers/quantityRestrictionPopover";
import { getProductMinQtyAndStep } from "helpers/getProductMinQtyAndStep";

const { setSelectedProductsToAddToCart, setCartSubtotal } =
  orderFormDataActions;
const { addProductToCartAction } = productListActions;

declare var WWOF_Frontend_Options: any;

const AddToCart = (props: any) => {
  const {
    orderFormId,
    product,
    actions,
    selectedVariation,
    orderFormData,
    getPropValue,
    formStyles,
    showModal,
    productModalStrings,
  } = props;

  const [stockQuantity, setStockQuantity] = useState(product.stock_quantity);
  const [stockStatus, setStockStatus] = useState(product.stock_status);
  const [backorders, setBackorders] = useState(product.stock_status);

  const [quantity, setQuantity] = useState(1);
  const [quantityStep, setQuantityStep] = useState(1);
  const addToCartBtnRef = useRef<HTMLButtonElement>(null);

  // Popover
  const [popoverVisibility, setPopoverVisibility] = useState(false);
  const [popoverContent, setPopoverContent] = useState("");

  const decimalQuantity =
    getPropValue({
      formStyles,
      item: "quantity-input",
      prop: "decimalQuantity",
    }) ?? false;

  const setQuantities = () => {
    if (
      typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
      WWOF_Frontend_Options.wholesale_role !== ""
    ) {
      let selectedProduct =
        orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id];

      if (typeof selectedProduct !== "undefined") {
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

  // When variation is selected update set stock qty, stock status, backorder
  useEffect(() => {
    try {
      setQuantities();

      let selectedProductVariationID =
        orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id]
          ?.variationID;
      let formProductVariations =
        orderFormData?.formProducts?.[orderFormId]?.variations?.[product.id];

      if (
        product.type === "variable" &&
        typeof selectedProductVariationID !== "undefined" &&
        typeof formProductVariations !== "undefined"
      ) {
        let vdata = formProductVariations.find((data: any) => {
          return data.id === selectedProductVariationID;
        });

        if (typeof vdata !== "undefined") {
          setStockQuantity(vdata.stock_quantity);
          setStockStatus(vdata.stock_status);
          setBackorders(vdata.backorders);
        }
      } else {
        setStockQuantity(product.stock_quantity);
        setStockStatus(product.stock_status);
        setBackorders(product.backorders);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId][product.id]]);

  useEffect(() => {
    try {
      setQuantities();
      if (showModal === false) {
        setPopoverVisibility(false);
      }
    } catch (e) {
      console.log(e);
    }
  }, [showModal]);

  const onQuantityChange = (quantity: number) => {
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

      let selectedProduct =
        orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id];

      if (typeof selectedProduct !== "undefined") {
        // If we have this data in redux state then update quantity
        actions.setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...orderFormData.formSelectedProducts[orderFormId],
            [product.id]: {
              ...selectedProduct,
              quantity: inputQuantity,
            },
          },
        });
      } else {
        // Else add these data
        actions.setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...orderFormData.formSelectedProducts[orderFormId],
            [product.id]: {
              productID: product.id,
              productTitle: product.name,
              productType: product.type,
              quantity: inputQuantity,
              stockStatus: product.stock_status,
              wholesaleData:
                product.type === "simple" &&
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

  return (
    <div style={{ margin: "1rem 0px" }} className="add-to-cart">
      {stockStatus === "outofstock" ? (
        <>
          <div className="quantity add-to-cart">
            <div className="stock-status">
              <span className={stockStatus}>
                <FrownOutlined style={{ color: "#e2401c" }} />{" "}
                {productModalStrings?.out_of_stock}
              </span>
            </div>
          </div>
        </>
      ) : (
        <>
          <div className="input-quantity add-to-cart">
            <Popover
              content={
                <>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: popoverContent,
                    }}
                  />
                  <Button onClick={() => setPopoverVisibility(false)}>
                    {productModalStrings?.close}
                  </Button>
                </>
              }
              title={productModalStrings?.quantity_restriction}
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
            <Button
              size="large"
              ref={addToCartBtnRef}
              type="primary"
              onClick={(e: any) => {
                addProductToCart({ ...props, addToCartBtnRef });
              }}
            >
              {productModalStrings?.add_to_cart_text}
            </Button>{" "}
            {stockQuantity === 0 && backorders === "notify" ? (
              <div className="stock-status">
                <span className={stockStatus}>
                  {productModalStrings?.backorder}
                </span>
              </div>
            ) : (
              ""
            )}
            {stockStatus === "instock" && stockQuantity > 0 ? (
              <div className="stock-status">
                <span className={stockStatus}>
                  <SmileOutlined style={{ color: "#0f834d" }} /> {stockQuantity}{" "}
                  {productModalStrings?.in_stock}
                </span>
              </div>
            ) : (
              ""
            )}
          </div>
        </>
      )}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  addProductToCartStrings: store.i18n.frontend.add_product_to_cart,
  qtyRestrictionStrings: store.i18n.frontend.quantity_restriction_popover,
  productModalStrings: store.i18n.frontend.product_modal.add_to_cart,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setSelectedProductsToAddToCart,
      addProductToCartAction,
      setCartSubtotal,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(AddToCart);
