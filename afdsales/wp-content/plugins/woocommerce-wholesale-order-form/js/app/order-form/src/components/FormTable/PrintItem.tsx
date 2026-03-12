import AddToCartButton from "./Elements/AddToCartButton";
import AddToCartCheckbox from "./Elements/AddToCartCheckbox";
import QuantityInput from "./Elements/QuantityInput";
import ShortDescription from "./Elements/ShortDescription";
import Price from "./Elements/Price";
import ProductImage from "./Elements/ProductImage";
import InStockAmount from "./Elements/InStockAmount";
import ProductSku from "./Elements/ProductSku";
import ProductName from "./Elements/ProductName";
import VariationDropdown from "./Elements/VariationDropdown";
import ProductMeta from "./Elements/ProductMeta";

const PrintItem = (props: any) => {
  const { itemId } = props;

  const itemProps = { ...props };

  const displayItem = (itemId: string) => {
    switch (itemId) {
      // Table Elements
      case "product-image":
        return <ProductImage {...itemProps} />;
      case "product-name":
        return <ProductName {...itemProps} />;
      case "sku":
        return <ProductSku {...itemProps} />;
      case "in-stock-amount":
        return <InStockAmount {...itemProps} />;
      case "price":
        return <Price {...itemProps} />;
      case "quantity-input":
        return <QuantityInput {...itemProps} />;
      case "add-to-cart-button":
        return <AddToCartButton {...itemProps} />;
      case "variation-dropdown":
        return <VariationDropdown {...itemProps} />;
      case "short-description":
        return <ShortDescription {...itemProps} />;
      case "add-to-cart-checkbox":
        return <AddToCartCheckbox {...itemProps} />;
      case "product-meta":
        return <ProductMeta {...itemProps} />;
      default:
        return <></>;
    }
  };

  return <>{displayItem(itemId)}</>;
};

export default PrintItem;
