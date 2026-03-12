// This variable is loaded in wp wp_enqueue_scripts via wp_localize_script
declare var WWOF_Frontend_Options: any;

export const getProductMinQtyAndStep = (props: any) => {
  const { productType, wholesaleData, variationData, variationID } = props;

  let minOrderQty: number = 1;
  let orderQtyStep: number = 1;

  // Simple product min order qty and step
  if (productType === "simple") {
    if (
      typeof wholesaleData.wholesale_minimum_order_quantity !== "undefined" &&
      typeof wholesaleData.wholesale_minimum_order_quantity[
        WWOF_Frontend_Options.wholesale_role
      ] !== "undefined"
    ) {
      minOrderQty = parseFloat(
        wholesaleData.wholesale_minimum_order_quantity[
          WWOF_Frontend_Options.wholesale_role
        ]
      );
    }

    if (
      typeof wholesaleData.wholesale_order_quantity_step !== "undefined" &&
      typeof wholesaleData.wholesale_order_quantity_step[
        WWOF_Frontend_Options.wholesale_role
      ] !== "undefined"
    ) {
      orderQtyStep = parseFloat(
        wholesaleData.wholesale_order_quantity_step[
          WWOF_Frontend_Options.wholesale_role
        ]
      );
    }
  } else if (productType === "variable") {
    if (variationID) {
      if (typeof WWOF_Frontend_Options.wholesale_role !== "undefined") {
        // Min Order Qty
        if (
          typeof variationData !== "undefined" &&
          typeof variationData.wholesale_minimum_order_quantity !==
            "undefined" &&
          typeof variationData.wholesale_minimum_order_quantity[
            WWOF_Frontend_Options.wholesale_role
          ] !== "undefined"
        ) {
          let qty =
            variationData.wholesale_minimum_order_quantity[
              WWOF_Frontend_Options.wholesale_role
            ];

          if (qty !== 1) {
            minOrderQty = parseFloat(qty);
          }
        }

        // Qty Step
        if (
          typeof variationData !== "undefined" &&
          typeof variationData.wholesale_order_quantity_step !== "undefined" &&
          typeof variationData.wholesale_order_quantity_step[
            WWOF_Frontend_Options.wholesale_role
          ] !== "undefined"
        ) {
          orderQtyStep = parseFloat(
            variationData.wholesale_order_quantity_step[
              WWOF_Frontend_Options.wholesale_role
            ]
          );
        }
      }
    }

    // Variable parent product min order qty and step
    if (minOrderQty === 1) {
      if (
        typeof wholesaleData !== "undefined" &&
        typeof wholesaleData.variable_level_wholesale_minimum_order_quantity !==
          "undefined" &&
        typeof wholesaleData.variable_level_wholesale_minimum_order_quantity[
          WWOF_Frontend_Options.wholesale_role
        ] !== "undefined" &&
        minOrderQty === 1
      ) {
        minOrderQty = parseFloat(
          wholesaleData.variable_level_wholesale_minimum_order_quantity[
            WWOF_Frontend_Options.wholesale_role
          ]
        );
      }

      if (
        typeof wholesaleData !== "undefined" &&
        typeof wholesaleData.variable_level_wholesale_order_quantity_step !==
          "undefined" &&
        typeof wholesaleData.variable_level_wholesale_order_quantity_step[
          WWOF_Frontend_Options.wholesale_role
        ] !== "undefined" &&
        orderQtyStep === 1
      ) {
        orderQtyStep = parseFloat(
          wholesaleData.variable_level_wholesale_order_quantity_step[
            WWOF_Frontend_Options.wholesale_role
          ]
        );
      }
    }
  }

  return { minOrderQty, orderQtyStep };
};
