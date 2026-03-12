declare var WWOF_Frontend_Options: any;

export const getProductMinOrderQuantity = (props: any) => {
  const { product } = props;

  if (
    product.type === "simple" &&
    typeof product.wholesale_data !== "undefined"
  ) {
    if (
      typeof product.wholesale_data.wholesale_minimum_order_quantity !==
        "undefined" &&
      typeof product.wholesale_data.wholesale_minimum_order_quantity[
        WWOF_Frontend_Options.wholesale_role
      ] !== "undefined"
    ) {
      let minOrderQty =
        product.wholesale_data.wholesale_minimum_order_quantity[
          WWOF_Frontend_Options.wholesale_role
        ];
      return minOrderQty;
    }
  } else if (
    product.type === "variable" &&
    typeof product.wholesale_data !== "undefined"
  ) {
    // Variable parent product min order qty
    if (
      typeof product.wholesale_data
        .variable_level_wholesale_minimum_order_quantity !== "undefined" &&
      typeof product.wholesale_data
        .variable_level_wholesale_minimum_order_quantity[
        WWOF_Frontend_Options.wholesale_role
      ] !== "undefined"
    ) {
      let minOrderQty =
        product.wholesale_data.variable_level_wholesale_minimum_order_quantity[
          WWOF_Frontend_Options.wholesale_role
        ];
      return minOrderQty;
    }
  }

  return 1;
};
