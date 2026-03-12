const getPropValue = (props: any) => {
  const { properties, prop } = props;

  if (typeof properties === "undefined") return null;
  else if (
    typeof properties !== "undefined" &&
    typeof properties[prop] !== "undefined"
  )
    return properties[prop];
  else return null;
};

export default function getComponentName(props: any) {
  const { itemId, properties } = props;

  let name = "";
  switch (itemId) {
    case "product-image":
      name = getPropValue({ properties, prop: "columnHeading" }) || "Image";
      break;
    case "product-name":
      name =
        getPropValue({ properties, prop: "columnHeading" }) || "Product Name";
      break;
    case "sku":
      name = getPropValue({ properties, prop: "columnHeading" }) || "SKU";
      break;
    case "in-stock-amount":
      name = getPropValue({ properties, prop: "columnHeading" }) || "In Stock";
      break;
    case "price":
      name = getPropValue({ properties, prop: "columnHeading" }) || "Price";
      break;
    case "quantity-input":
      name = getPropValue({ properties, prop: "columnHeading" }) || "Quantity";
      break;
    case "add-to-cart-button":
      name =
        getPropValue({ properties, prop: "columnHeading" }) ||
        "Add To Cart Button";
      break;
    case "variation-dropdown":
      name = getPropValue({ properties, prop: "columnHeading" }) || "Options";
      break;
    case "short-description":
      name =
        getPropValue({ properties, prop: "columnHeading" }) || "Description";
      break;
    case "product-meta":
      name =
        getPropValue({ properties, prop: "columnHeading" }) || "Product Meta";
      break;
    case "global-attribute":
    case "add-to-cart-checkbox":
    default:
      name = "";
  }

  return name;
}
