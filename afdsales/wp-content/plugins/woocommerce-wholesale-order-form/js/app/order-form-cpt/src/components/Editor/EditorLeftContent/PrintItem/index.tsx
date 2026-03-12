// Header Footer Elements
import OrderFormPagination from "./HeaderFooterElements/OrderFormPagination";
import CategoryFilter from "./HeaderFooterElements/CategoryFilter";
import SearchInput from "./HeaderFooterElements/SearchInput";
import AddSelectedToCart from "./HeaderFooterElements/AddSelectedToCart";
import CartSubtotal from "./HeaderFooterElements/CartSubtotal";
import ProductCount from "./HeaderFooterElements/ProductCount";
import SearchButton from "./HeaderFooterElements/SearchButton";

// Table
import ProductImage from "./TableElements/ProductImage";
import ProductName from "./TableElements/ProductName";
import ProductSKU from "./TableElements/ProductSKU";
import InStockAmount from "./TableElements/InStockAmount";
import Price from "./TableElements/Price";
import QuantityInput from "./TableElements/QuantityInput";
import AddToCartButton from "./TableElements/AddToCartButton";
import VariationDropdown from "./TableElements/VariationDropdown";
import ShortDescription from "./TableElements/ShortDescription";
import AddToCartCheckbox from "./TableElements/AddToCartCheckbox";
import ProductMeta from "./TableElements/ProductMeta";

// Redux
import { connect } from "react-redux";

const useStyleValue = (style: any) => {
  let val = "auto";

  if (style === "") return val;

  if (style.type === "auto" || style.type === "inherit") return val;

  if (style.value !== undefined)
    return style.type === "pixels" ? style.value + "px" : style.value + "%";

  return val;
};

const getPropValue = (props: any) => {
  const { properties, prop } = props;

  if (typeof properties === "undefined") return null;
  else if (typeof properties?.[prop] !== "undefined") return properties[prop];
  else return null;
};

const PrintItem = (props: any) => {
  const {
    item,
    styles,
    properties,
    products,
    pagination,
    orderForm,
    boxStyles,
    elementStyles,
    hoveredRow,
    setHoveredRow,
    headerHeight,
    setHeaderHeight,
  } = props;

  const onMouseEnter = (props: any) => {
    const { e, setHoveredRow } = props;
    setHoveredRow(e.target.getAttribute("data-productid"));
  };

  const onMouseLeave = (props: any) => {
    const { setHoveredRow } = props;
    setHoveredRow("");
  };

  let tableItemArgs = {
    getPropValue,
    properties,
    boxStyles,
    elementStyles,
    products,
    hoveredRow,
    setHoveredRow,
    onMouseEnter,
    onMouseLeave,
    headerHeight,
    setHeaderHeight,
    useStyleValue,
  };

  if (typeof item === "undefined") return <></>;

  switch (item.id) {
    // HEADER AND FOOTER ELEMENTS
    case "search-input":
      return (
        <SearchInput
          style={{ ...styles }}
          getPropValue={getPropValue}
          properties={properties}
        />
      );
    case "category-filter":
      return (
        <CategoryFilter
          style={{ ...styles }}
          products={products}
          getPropValue={getPropValue}
          properties={properties}
        />
      );
    case "add-selected-to-cart-button":
      return (
        <AddSelectedToCart
          getPropValue={getPropValue}
          properties={properties}
          style={{ ...styles }}
        />
      );
    case "cart-subtotal":
      return (
        <CartSubtotal
          orderForm={orderForm}
          getPropValue={getPropValue}
          properties={properties}
          styles={styles}
        />
      );

    case "product-count":
      return (
        <ProductCount
          properties={properties}
          styles={styles}
          pagination={pagination}
        />
      );

    case "pagination":
      return (
        <OrderFormPagination
          styles={{ ...styles }}
          products={products}
          getPropValue={getPropValue}
        />
      );

    case "search-button":
      return (
        <SearchButton
          properties={properties}
          getPropValue={getPropValue}
          style={{ ...styles }}
        />
      );

    // TABLE ELEMENTS
    case "product-image":
      return <ProductImage {...tableItemArgs} />;

    case "product-name":
      return <ProductName {...tableItemArgs} />;

    case "sku":
      return <ProductSKU {...tableItemArgs} />;

    case "in-stock-amount":
      return <InStockAmount {...tableItemArgs} />;

    case "price":
      return <Price {...tableItemArgs} />;

    case "quantity-input":
      return <QuantityInput {...tableItemArgs} />;

    case "add-to-cart-button":
      return <AddToCartButton {...tableItemArgs} />;

    case "variation-dropdown":
      return <VariationDropdown {...tableItemArgs} />;

    case "short-description":
      return <ShortDescription {...tableItemArgs} />;

    case "add-to-cart-checkbox":
      return <AddToCartCheckbox {...tableItemArgs} />;

    case "product-meta":
      return <ProductMeta {...tableItemArgs} />;

    default:
      return <></>;
  }
};

const mapStateToProps = (store: any, props: any) => ({
  styling: store.styling,
});

export default connect(mapStateToProps)(PrintItem);
