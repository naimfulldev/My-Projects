import { getProductMinQtyAndStep } from "./getProductMinQtyAndStep";

const quantityRestrictionPopover = (props: any) => {
  const {
    getPropValue,
    formStyles,
    product,
    orderFormData,
    orderFormId,
    quantity,
    qtyRestrictionStrings,
  } = props;

  let variationID = 0;
  let variationData: any = [];

  let quantityRestriction = getPropValue({
    formStyles,
    item: "quantity-input",
    prop: "quantityRestriction",
  });

  if (quantityRestriction === undefined || quantityRestriction === null) {
    quantityRestriction = true;
  }

  const selectedProducts = orderFormData?.formSelectedProducts?.[orderFormId];
  const selectedProduct = selectedProducts?.[product.id];

  if (
    typeof selectedProducts !== "undefined" &&
    typeof selectedProduct !== "undefined"
  ) {
    if (typeof selectedProduct["variationID"] !== "undefined")
      variationID = selectedProduct["variationID"];
  }

  const variations =
    orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[product.id];

  if (
    typeof variations !== "undefined" &&
    variationID != 0 &&
    product.type === "variable"
  ) {
    variationData = variations.find((data: any) => {
      return data.id === variationID;
    });

    variationData =
      typeof variationData.wholesale_data !== "undefined"
        ? variationData.wholesale_data
        : [];
  }

  // Quantity Step Restriction
  const { minOrderQty, orderQtyStep } = getProductMinQtyAndStep({
    productType: product.type,
    variationID,
    wholesaleData:
      typeof product.wholesale_data !== "undefined"
        ? product.wholesale_data
        : [],
    variationData,
  });

  // Return immediately if qty and step has value of 1
  if (minOrderQty === 1 && orderQtyStep === 1)
    return { visible: false, content: "" };

  let multiplier: any = (quantity - minOrderQty) / orderQtyStep;
  multiplier = parseInt(multiplier, 10);
  let nearestLow = minOrderQty + orderQtyStep * multiplier;
  let nearestHigh = minOrderQty + orderQtyStep * (multiplier + 1);
  let excessQty = quantity - minOrderQty;

  if (quantityRestriction) {
    if (minOrderQty > 1 && quantity < minOrderQty) {
      return {
        visible: true,
        content: `${qtyRestrictionStrings?.quantity_of} <b>${quantity}</b> ${qtyRestrictionStrings?.is_invalid} <b>${minOrderQty}</b>.`,
      };
    } else if (
      minOrderQty > 1 &&
      orderQtyStep > 1 &&
      excessQty % orderQtyStep !== 0
    ) {
      return {
        visible: true,
        content: `<p>${qtyRestrictionStrings?.quantity_of} <b>${quantity}</b> ${qtyRestrictionStrings?.is_invalid} <b>${minOrderQty}</b> ${qtyRestrictionStrings?.increments_of} <b>${orderQtyStep}</b>.</p><p>${qtyRestrictionStrings?.two_nearest_values} <b>${nearestLow}</b> ${qtyRestrictionStrings?.and} <b>${nearestHigh}</b>.</p>`,
      };
    } else return { error: false, visible: false, content: "" };
  } else {
    if (minOrderQty > 1 && quantity < minOrderQty) {
      return {
        visible: true,
        content: `${qtyRestrictionStrings?.quantity_of} <b>${quantity}</b> ${qtyRestrictionStrings?.not_within_range} <b>${minOrderQty}</b>.`,
      };
    } else if (
      minOrderQty > 1 &&
      orderQtyStep > 1 &&
      excessQty % orderQtyStep !== 0
    ) {
      return {
        visible: true,
        content: `${qtyRestrictionStrings?.quantity_of} <b>${quantity}</b> ${qtyRestrictionStrings?.not_within_range} <b>${minOrderQty}</b> ${qtyRestrictionStrings?.increments_of} <b>${orderQtyStep}</b>.`,
      };
    }
  }

  return {
    visible: false,
    content: "",
  };
};

export default quantityRestrictionPopover;
