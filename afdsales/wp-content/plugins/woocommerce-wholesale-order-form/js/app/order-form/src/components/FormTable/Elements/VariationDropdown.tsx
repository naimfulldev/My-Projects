import ComboVariationsDropdown from "./shared/ComboVariationsDropdown";
import StandardVariationsDropdown from "./shared/StandardVariationsDropdown";

import { connect } from "react-redux";

const VariationDropdown = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
  } = props;

  const formProductsVariations =
    orderFormData?.formProducts?.[orderFormId]?.["variations"];
  if (
    product.type !== "variable" ||
    typeof formProductsVariations === "undefined"
  )
    return <>&nbsp;</>;

  const variations = formProductsVariations?.[product.id];

  if (typeof variations === "undefined" || variations.length <= 0)
    return <>&nbsp;</>;

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

  const variationSelector =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "variationSelectorStyle",
    }) || "combo";

  return (
    <div className="item variation-dropdown" style={{ ...alignmentCSS }}>
      {variationSelector === "combo" ? (
        <ComboVariationsDropdown {...props} variations={variations} />
      ) : (
        <StandardVariationsDropdown {...props} variations={variations} />
      )}
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  orderFormData: store.orderFormData,
  formStyles: store.orderFormData.formStyles[props.orderFormId],
});

export default connect(mapStateToProps)(VariationDropdown);
