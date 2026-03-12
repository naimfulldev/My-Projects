import { getProductMinQtyAndStep } from "./getProductMinQtyAndStep";
import { Button, notification } from "antd";
import { ShoppingCartOutlined } from "@ant-design/icons";

// This variable is loaded in wp wp_enqueue_scripts via wp_localize_script
declare var WWOF_Frontend_Options: any;

export const addProductToCart = (props: any) => {
  const {
    orderForm,
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    actions,
    addProductToCartStrings,
  } = props;

  const {
    add_to_cart_failed,
    select_variation,
    zero_qty_error,
    min_qty_error,
    step_error,
    and,
    successfully_added,
    view_cart,
    empty_error,
    cannot_add_to_cart,
  } = addProductToCartStrings;

  let variationID = 0;
  let quantity = 1;
  let variationName: any = [];
  let variationData: any = [];

  const selectedProduct =
    orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id];

  if (typeof selectedProduct !== "undefined") {
    if (typeof selectedProduct?.["variationID"] !== "undefined")
      variationID = selectedProduct?.["variationID"];

    if (typeof selectedProduct?.["quantity"] !== "undefined")
      quantity = parseFloat(selectedProduct?.["quantity"]);
  } else {
    // Set quantity if Wholesale Min Order Quantity is set
    const wholesaleMinOrderQty =
      product?.wholesale_data?.wholesale_minimum_order_quantity;
    if (
      typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
      typeof wholesaleMinOrderQty !== "undefined" &&
      typeof wholesaleMinOrderQty[WWOF_Frontend_Options.wholesale_role] !==
        "undefined"
    )
      quantity = parseFloat(
        wholesaleMinOrderQty[WWOF_Frontend_Options.wholesale_role]
      );
  }

  const variations = orderFormData.formProducts[orderFormId]["variations"];

  if (
    typeof variations !== "undefined" &&
    typeof variations[product.id] !== "undefined" &&
    variationID != 0 &&
    product.type === "variable"
  ) {
    variationData = variations[product.id].find((data: any) => {
      return data.id === variationID;
    });

    if (variationData !== undefined) {
      variationName = variationData.attributes.map((attributes: any) => {
        return attributes.name + ": " + attributes.option;
      });
    }
  } else if (product.type === "variation") {
    variationName = product.attributes.map((attributes: any) => {
      return `<strong>${attributes.name}</strong>:  ${attributes.option}`;
    });
  }

  let quantityRestriction = getPropValue({
    formStyles,
    item: "quantity-input",
    prop: "quantityRestriction",
  });

  if (quantityRestriction === undefined || quantityRestriction === null) {
    quantityRestriction = true;
  }

  // Quantity Step Restriction
  const { minOrderQty, orderQtyStep } = getProductMinQtyAndStep({
    productType: product.type,
    wholesaleData:
      typeof product.wholesale_data !== "undefined"
        ? product.wholesale_data
        : [],
    variationData:
      typeof variationData.wholesale_data !== "undefined"
        ? variationData.wholesale_data
        : [],
    variationID,
    quantity,
  });

  let multiplier: any = (quantity - minOrderQty) / orderQtyStep;
  multiplier = parseInt(multiplier, 10);
  let nearestLow = minOrderQty + orderQtyStep * multiplier;
  let nearestHigh = minOrderQty + orderQtyStep * (multiplier + 1);
  let excessQty = quantity - minOrderQty;
  let valid = true;

  if (
    product.type === "variable" &&
    (variationID <= 0 || typeof variationID === "undefined")
  ) {
    notification["error"]({
      message: add_to_cart_failed,
      description: select_variation,
      duration: 10,
    });
    valid = false;
  } else if (quantity === 0) {
    notification["error"]({
      message: add_to_cart_failed,
      description: (
        <div
          dangerouslySetInnerHTML={{
            __html: zero_qty_error,
          }}
        />
      ),
      duration: 10,
    });
    valid = false;
  } else if (quantityRestriction) {
    if (minOrderQty > 1 && quantity < minOrderQty) {
      notification["error"]({
        message: add_to_cart_failed,
        description: (
          <div
            dangerouslySetInnerHTML={{
              __html: `${min_qty_error} <b>${minOrderQty}</b>.`,
            }}
          />
        ),
        duration: 10,
      });
      valid = false;
    } else if (
      minOrderQty > 1 &&
      orderQtyStep > 1 &&
      excessQty % orderQtyStep !== 0
    ) {
      notification["error"]({
        message: add_to_cart_failed,
        description: (
          <div
            dangerouslySetInnerHTML={{
              __html: `${step_error} <b>${nearestLow}</b> ${and} <b>${nearestHigh}</b>.`,
            }}
          />
        ),
        duration: 10,
      });
      valid = false;
    }
  }

  if (valid) {
    actions.addProductToCartAction({
      product_type: product.type,
      product_id: product.id,
      variation_id: variationID,
      quantity: quantity,
      form_settings: orderFormData.formSettings[orderFormId],
      successCB: (args: any) => {
        notification["success"]({
          message: successfully_added,
          description: (
            <div>
              <div
                dangerouslySetInnerHTML={{
                  __html: `<b>${
                    product.name
                  }</b> x ${quantity}<br/>${variationName.join("<br/>")}`,
                }}
              />
              <a href={orderForm.cartURL} target="_blank">
                <Button style={{ marginTop: "10px" }}>
                  {view_cart}
                  <ShoppingCartOutlined />
                </Button>
              </a>
            </div>
          ),
          duration: 10,
        });

        // Trigger added_to_cart
        const adding_to_cart = new CustomEvent("adding_to_cart", {
          bubbles: true,
          detail: {
            fragments: args.data.fragments,
            cart_hash: args.data.cart_hash,
            element: document.body,
          },
        });

        document.body.dispatchEvent(adding_to_cart);

        const added_to_cart = new CustomEvent("added_to_cart", {
          bubbles: true,
          detail: {
            fragments: args.data.fragments,
            cart_hash: args.data.cart_hash,
            element: document.body,
          },
        });

        // Update cart total by triggering the added_to_cart custom event of wc.
        document.body.dispatchEvent(added_to_cart);

        // Update Cart Subtotal
        actions.setCartSubtotal({
          [orderFormId]: {
            cartSubtotal: args.data.cart_subtotal_markup,
          },
        });
      },
      failCB: () => {
        if (quantity === null) {
          notification["error"]({
            message: add_to_cart_failed,
            description: empty_error,
            duration: 10,
          });
        } else if (product.type === "variable" && variationID <= 0) {
          notification["error"]({
            message: add_to_cart_failed,
            description: select_variation,
            duration: 10,
          });
        } else {
          notification["error"]({
            message: add_to_cart_failed,
            description: cannot_add_to_cart,
            duration: 10,
          });
        }
      },
    });
  }
};
