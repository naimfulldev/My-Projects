// Elements
import SearchInput from "./Elements/SearchInput";
import CategoryFilter from "./Elements/CategoryFilter";
import OrderFormPagination from "./Elements/OrderFormPagination";
import SearchButton from "./Elements/SearchButton";
import AddSelectedProductsToCartButton from "./Elements/AddSelectedProductsToCartButton";
import CartSubtotal from "./Elements/CartSubtotal";
import ProductCount from "./Elements/ProductCount";

const getPropValue = (props: any) => {
  const { properties, prop } = props;

  const propValue = properties?.[prop];

  return typeof propValue !== "undefined" ? propValue : null;
};

const PrintItem = (props: any) => {
  const { item, styles, properties, orderFormId, fetchProducts } = props;

  const displayItem = (id: string) => {
    switch (item.id) {
      // Header/Footer Elements
      case "search-input":
        let submitOnEnter = getPropValue({ properties, prop: "submitOnEnter" });
        if (submitOnEnter === null) submitOnEnter = true;

        let allowSkuSearch = getPropValue({ properties, prop: "skuSearch" });
        if (allowSkuSearch === null) allowSkuSearch = true;

        return (
          <SearchInput
            getPropValue={getPropValue}
            properties={properties}
            orderFormId={orderFormId}
            styles={styles}
            fetchProducts={fetchProducts}
            submitOnEnter={submitOnEnter}
            allowSkuSearch={allowSkuSearch}
          />
        );

      case "category-filter":
        let submitOnChange = getPropValue({
          properties,
          prop: "submitOnChange",
        });

        if (submitOnChange === null) submitOnChange = true;

        return (
          <CategoryFilter
            getPropValue={getPropValue}
            properties={properties}
            orderFormId={orderFormId}
            styles={styles}
            fetchProducts={fetchProducts}
            submitOnChange={submitOnChange}
            defaultCategory={getPropValue({
              properties,
              prop: "defaultCategory",
            })}
            includedCategories={getPropValue({
              properties,
              prop: "includedCategories",
            })}
            excludedCategories={getPropValue({
              properties,
              prop: "excludedCategories",
            })}
          />
        );

      case "add-selected-to-cart-button":
        return (
          <AddSelectedProductsToCartButton
            getPropValue={getPropValue}
            properties={properties}
            orderFormId={orderFormId}
            styles={styles}
          />
        );

      case "cart-subtotal":
        return (
          <CartSubtotal
            getPropValue={getPropValue}
            properties={properties}
            orderFormId={orderFormId}
            styles={styles}
          />
        );

      case "product-count":
        return <ProductCount orderFormId={orderFormId} styles={styles} />;

      case "pagination":
        return (
          <OrderFormPagination
            getPropValue={getPropValue}
            properties={properties}
            productsPerPage={
              getPropValue({ properties, prop: "productsPerPage" }) || 12
            }
            orderFormId={orderFormId}
            styles={styles}
            fetchProducts={fetchProducts}
          />
        );

      case "search-button":
        return (
          <SearchButton
            orderFormId={orderFormId}
            styles={styles}
            getPropValue={getPropValue}
            properties={properties}
            fetchProducts={fetchProducts}
          />
        );

      default:
        return <></>;
    }
  };

  return <>{typeof item !== "undefined" ? displayItem(item.id) : ""}</>;
};

export default PrintItem;
