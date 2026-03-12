import React from "react";

// Redux
import { connect } from "react-redux";

// Component Options
import Placeholder from "./Placeholder";
import ButtonText from "./ButtonText";
import ProductsPerPage from "./ProductsPerPage";
import ColumnHeadingText from "./ColumnHeadingText";
import SubmitOnEnter from "./SubmitOnEnter";
import SubmitOnChange from "./SubmitOnChange";
import DefaultCategory from "./DefaultCategory";
import IncludedCategories from "./IncludedCategories";
import ShowClearButton from "./ShowClearButton";
import ClearButtonText from "./ClearButtonText";
import PreText from "./PreText";
import EmptyCartText from "./EmptyCartText";
import TaxDisplay from "./TaxDisplay";
import SubtotalSuffix from "./SubtotalSuffix";
import ImageSize from "./ImageSize";
import ImageClickAction from "./ImageClickAction";
import Sortable from "./Sortable";
import WholesaleQuantityBasedPricingTable from "./WholesaleQuantityBasedPricingTable";
import MaxCharacters from "./MaxCharacters";
import VariationSelectorStyle from "./VariationSelectorStyle";
import SmartVisibility from "./SmartVisibility";
import OutOfStockText from "./OutOfStockText";
import ExcludedCategories from "./ExcludedCategories";
import ShowMinReqNotice from "./ShowMinReqNotice";
import ShowFormHeader from "./ShowFormHeader";
import ShowFormFooter from "./ShowFormFooter";
import QuantityRestriction from "./QuantityRestriction";
import DecimalQuantity from "./DecimalQuantity";
import ProductMetaDataSetting from "./ProductMetaDataSetting";
import SkuSearch from "./SKUSearch";

// Helpers
import { getPropValue } from "helpers/getPropValue";
import { updateStyling } from "helpers/updateStyling";
import { getCategoryList } from "helpers/getCategoryList";

const ShowOptions = (props: any) => {
  const {
    styling,
    setStyles,
    showOptionsStrings,
    headerFooterElementsStrings,
    tableElementsStrings,
  } = props;

  const { no_options } = showOptionsStrings;

  const id = styling.item.id;

  const styleProps = {
    styling,
    setStyles,
    id,
    updateStyling,
    getPropValue,
  };

  const displayOptions = () => {
    let options = [];

    if (styling.item.type === "ROW") return <>{no_options}</>;

    // Sections
    if (styling.item.type === "SECTION") {
      switch (styling.item.section) {
        case "formHeader":
          // Form Header Settings
          options.push(
            <ShowFormHeader {...styleProps} target="props" />,
            <ShowMinReqNotice {...styleProps} target="props" />
          );
          break;
        case "formFooter":
          // Form Footer Settings
          options.push(<ShowFormFooter {...styleProps} target="props" />);
          break;
        case "formTable":
          // Form Table Settings
          options.push(
            <IncludedCategories
              {...styleProps}
              getCategoryList={getCategoryList}
              target="props"
            />
          );
          break;
        default:
          break;
      }
    } else if (styling.item.type === "ITEM") {
      // Per Component/Element
      switch (styling.item.itemId) {
        // Header / Footer Elements
        case "search-input":
          options.push(
            <SkuSearch {...styleProps} target="props" />,
            <SubmitOnEnter {...styleProps} target="props" />,
            <Placeholder
              {...styleProps}
              target="props"
              placeholderText={
                headerFooterElementsStrings?.search_input?.search_button_text
              }
            />
          );
          break;
        case "category-filter":
          options.push(
            <SubmitOnChange {...styleProps} target="props" />,
            <Placeholder
              {...styleProps}
              target="props"
              placeholderText={
                headerFooterElementsStrings?.category_filter?.placeholder_text
              }
            />,
            <DefaultCategory
              {...styleProps}
              getCategoryList={getCategoryList}
              target="props"
            />,
            <IncludedCategories
              {...styleProps}
              getCategoryList={getCategoryList}
              target="props"
            />,
            <ExcludedCategories
              {...styleProps}
              getCategoryList={getCategoryList}
              target="props"
            />
          );
          break;
        case "add-selected-to-cart-button":
          options.push(<ButtonText {...styleProps} target="props" />);
          break;
        case "cart-subtotal":
          options.push(
            <PreText {...styleProps} target="props" />,
            <EmptyCartText {...styleProps} target="props" />,
            <TaxDisplay {...styleProps} target="props" />,
            <SubtotalSuffix {...styleProps} target="props" />
          );
          break;
        case "product-count":
          break;
        case "pagination":
          options.push(<ProductsPerPage {...styleProps} target="props" />);
          break;
        case "search-button":
          options.push(
            <ButtonText
              {...styleProps}
              target="props"
              buttonText="searchButtonText"
            />,
            <ShowClearButton {...styleProps} target="props" />,
            <ClearButtonText {...styleProps} target="props" />
          );
          break;
        // Widget
        case "attribute-filter":
          break;
        case "cart-widget":
          break;
        case "filter-products-by-attribute":
          break;
        case "filter-products-by-price":
          options.push(<Placeholder {...styleProps} target="props" />);
          break;
        // Table Elements
        case "product-image":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.product_image?.column_heading ?? ""
              }
            />,
            <ImageSize {...styleProps} target="props" />,
            <ImageClickAction {...styleProps} target="props" />
          );
          break;
        case "product-name":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.product_name?.column_heading ?? ""
              }
            />,
            <Sortable {...styleProps} target="props" />,
            <ImageClickAction {...styleProps} target="props" />
          );
          break;
        case "sku":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.product_sku?.column_heading ?? ""
              }
            />,
            <Sortable {...styleProps} target="props" />
          );
          break;
        case "in-stock-amount":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.in_stock_amount?.column_heading ?? ""
              }
            />
          );
          break;
        case "price":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={tableElementsStrings?.price?.column_heading ?? ""}
            />,
            <Sortable {...styleProps} target="props" />,
            <WholesaleQuantityBasedPricingTable
              {...styleProps}
              target="props"
            />
          );
          break;
        case "quantity-input":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.quantity_input?.column_heading ?? ""
              }
            />,
            <OutOfStockText {...styleProps} target="props" />,
            <DecimalQuantity {...styleProps} target="props" />,
            <QuantityRestriction {...styleProps} target="props" />
          );
          break;
        case "add-to-cart-button":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.add_to_cart_button?.column_heading ?? ""
              }
            />,
            <ButtonText {...styleProps} target="props" />
          );
          break;
        case "variation-dropdown":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.variation_dropdown?.column_heading ?? ""
              }
            />,
            <VariationSelectorStyle {...styleProps} target="props" />,
            <SmartVisibility {...styleProps} target="props" />
          );
          break;
        case "standard-variation-dropdowns":
          options.push(<ColumnHeadingText {...styleProps} target="props" />);
          break;
        case "product-meta":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.product_meta?.column_heading ?? ""
              }
            />,
            <ProductMetaDataSetting {...styleProps} target="props" />
          );
          break;
        case "global-attribute":
          options.push(<ColumnHeadingText {...styleProps} target="props" />);
          break;
        case "short-description":
          options.push(
            <ColumnHeadingText
              {...styleProps}
              target="props"
              headingText={
                tableElementsStrings?.short_description?.column_heading ?? ""
              }
            />,
            <MaxCharacters {...styleProps} target="props" />
          );
          break;
        case "add-to-cart-checkbox":
          // no options
          break;
        default:
          options.push(<></>);
      }
    }

    if (options.length > 0)
      return options.map((component: any, key: number) =>
        React.cloneElement(component, { key })
      );
    else return <>{no_options}</>;
  };

  return <>{displayOptions()}</>;
};

const mapStateToProps = (store: any, props: any) => ({
  showOptionsStrings:
    store.i18n.backend.styling_and_option_controls.show_options,
  headerFooterElementsStrings: store.i18n.backend.header_footer_elements,
  tableElementsStrings: store.i18n.backend.table_elements,
});

export default connect(mapStateToProps)(ShowOptions);
