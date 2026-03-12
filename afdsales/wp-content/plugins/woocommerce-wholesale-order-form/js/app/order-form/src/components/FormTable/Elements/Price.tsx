import { useState, useEffect } from "react";
import { connect } from "react-redux";
import DisplayQuantityBasedTable from "./shared/DisplayQuantityBasedTable";

declare var WWOF_Frontend_Options: any;

const Price = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
  } = props;

  const [priceHtml, setPriceHtml] = useState(product.price_html);

  useEffect(() => {
    try {
      if (
        typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
        typeof product?.wholesale_data?.price_html !== "undefined" &&
        WWOF_Frontend_Options.wholesale_role !== ""
      ) {
        setPriceHtml(product.wholesale_data.price_html);
      }
    } catch (e) {
      console.log(e);
    }
  }, [WWOF_Frontend_Options.wholesale_role]);

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
        product.type === "variable" &&
        typeof selectedProduct?.variationID !== "undefined" &&
        selectedProduct?.variationID > 0
      ) {
        const variationID = selectedProduct?.variationID;
        const variations =
          orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
            product.id
          ];

        if (typeof variations !== "undefined") {
          const variationData = variations.find((variation: any) => {
            return variation.id === variationID;
          });

          if (
            typeof variationData !== "undefined" &&
            typeof variationData.wholesale_data !== "undefined"
          ) {
            let minOrderQty: any = "";
            let orderQtyStep: any = "";

            // Parent Min Order Qty and Step
            if (
              typeof product.wholesale_data !== "undefined" &&
              typeof variationData.wholesale_data
                .wholesale_minimum_order_quantity === "undefined" &&
              typeof product.wholesale_data
                .variable_level_wholesale_minimum_order_quantity !== "undefined"
            ) {
              // Parent Min Order Qty
              if (
                typeof product.wholesale_data
                  .variable_level_wholesale_minimum_order_quantity[
                  WWOF_Frontend_Options.wholesale_role
                ] !== "undefined"
              ) {
                minOrderQty =
                  '<span class="wholesale_price_minimum_order_quantity" style="display: block">Min: ' +
                  product.wholesale_data
                    .variable_level_wholesale_minimum_order_quantity[
                    WWOF_Frontend_Options.wholesale_role
                  ] +
                  "</span>";
              }

              // Parent Order Step
              if (
                typeof product.wholesale_data
                  .variable_level_wholesale_order_quantity_step !==
                  "undefined" &&
                typeof product.wholesale_data
                  .variable_level_wholesale_order_quantity_step[
                  WWOF_Frontend_Options.wholesale_role
                ] !== "undefined"
              ) {
                orderQtyStep =
                  '<span class="wholesale_price_order_quantity_step" style="display: block;">Increments of ' +
                  product.wholesale_data
                    .variable_level_wholesale_order_quantity_step[
                    WWOF_Frontend_Options.wholesale_role
                  ] +
                  "</span>";
              }
            }

            // Wholesale Price
            if (typeof variationData.wholesale_data.price_html !== "undefined")
              setPriceHtml(
                variationData.wholesale_data.price_html +
                  minOrderQty +
                  orderQtyStep
              );
            else setPriceHtml(variationData.price_html || variationData.price); // Regular Price
          } else if (typeof variationData !== "undefined") {
            // Regular Price
            if (typeof variationData.price !== "undefined")
              setPriceHtml(variationData.price);
            else setPriceHtml(variationData.price_html);
          }
        }
      } else {
        if (
          typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
          typeof product.wholesale_data !== "undefined" &&
          typeof product.wholesale_data.price_html !== "undefined" &&
          WWOF_Frontend_Options.wholesale_role !== ""
        ) {
          setPriceHtml(product.wholesale_data.price_html);
        } else {
          setPriceHtml(product.price_html);
        }
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  const alignment =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "justifyContent",
    }) || "center";

  const alignmentCSS = {
    textAlign:
      alignment === "flex-start"
        ? ("left" as const)
        : alignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  let showQuantityBasedTable = getPropValue({
    formStyles,
    item: itemId,
    prop: "showQuantityBasedPricing",
  });

  if (showQuantityBasedTable === null) {
    showQuantityBasedTable = true;
  }

  return (
    <div
      className="item product-price"
      style={{ minWidth: "150px", ...alignmentCSS }}
    >
      {product.type !== "variable" ||
      typeof orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
        product.id
      ] !== "undefined" ? (
        <div dangerouslySetInnerHTML={{ __html: priceHtml || " " }} />
      ) : (
        <div
          dangerouslySetInnerHTML={{
            __html: priceHtml,
          }}
        />
      )}

      <DisplayQuantityBasedTable
        {...props}
        showQuantityBasedTable={showQuantityBasedTable}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
});

export default connect(mapStateToProps)(Price);
