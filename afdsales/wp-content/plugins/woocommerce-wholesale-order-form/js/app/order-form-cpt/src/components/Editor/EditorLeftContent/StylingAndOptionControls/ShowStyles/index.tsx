import React from "react";
import Alignment from "./Alignment";
import Margin from "./Margin";
import Padding from "./Padding";
import FontSize from "./FontSize";
import Width from "./Width";
import ButtonColor from "./ButtonColor";
import ButtonTextColor from "./ButtonTextColor";
import { connect } from "react-redux";

const getStyleValue = (props: any) => {
  const { styling, id, target, style, extra } = props;

  if (extra) {
    const value = styling?.styles?.[id]?.[target]?.[style]?.[extra];
    if (typeof value !== "undefined") return value;
    else return null;
  } else {
    const value = styling?.styles?.[id]?.[target]?.[style];
    if (typeof value !== "undefined") return value;
    else return null;
  }
};

const updateStyling = (props: any) => {
  const { setStyles, styling, id, target, toUpdate } = props;

  if (typeof styling.styles[id] === "undefined") {
    const newData = {
      [id]: {
        [target]: {
          ...toUpdate,
        },
      },
    };

    setStyles({
      ...styling,
      styles: {
        ...styling.styles,
        ...newData,
      },
    });
  } else {
    setStyles({
      ...styling,
      styles: {
        ...styling.styles,
        [id]: {
          ...styling.styles[id],
          [target]: {
            ...styling.styles[id][target],
            ...toUpdate,
          },
        },
      },
    });
  }
};

const ShowStyles = (props: any) => {
  const {
    styling,
    setStyles,
    showStylesStrings,
    alignmentStrings,
    widthStrings,
  } = props;
  const id = styling.item.id;

  const styleProps = {
    styling,
    setStyles,
    id,
    updateStyling,
    getStyleValue,
  };

  const displayStyles = () => {
    if (styling.item.type === "SECTION") {
      switch (styling.item.section) {
        case "formTable":
          // Table settings
          return (
            <>
              <Width
                {...styleProps}
                target="box"
                label={widthStrings?.table_width}
                section="formTableSetting"
              />
              <Alignment
                {...styleProps}
                target="box"
                label={alignmentStrings?.table_alignment}
                section="formTableSetting"
              />
            </>
          );
        default:
          //Header and footer settings
          return (
            <>
              <Margin {...styleProps} target="box" />
              <Padding {...styleProps} target="box" />
            </>
          );
      }
    } else if (styling.item.type === "ROW") {
      return (
        <>
          <Width {...styleProps} target="box" />
          <Margin {...styleProps} target="box" />
          <Padding {...styleProps} target="box" />
        </>
      );
    } else if (styling.item.type === "ITEM") {
      let options = [];
      switch (styling.item.itemId) {
        // Header / Footer Elements
        case "search-button":
        case "search-input":
        case "category-filter":
        case "add-selected-to-cart-button":
        case "cart-subtotal":
        case "product-count":
        case "pagination":
        case "attribute-filter":
        case "cart-widget":
        case "filter-products-by-attribute":
        case "filter-products-by-price":
          options.push(
            <Width {...styleProps} target="box" />,
            <Width {...styleProps} target="element" />,
            <Alignment
              {...styleProps}
              target="box"
              section="formHeaderFooter"
            />,
            <FontSize {...styleProps} target="element" />,
            <Margin {...styleProps} target="box" />,
            <Padding {...styleProps} target="box" />
          );
          if (
            styling.item.itemId === "search-button" ||
            styling.item.itemId === "add-selected-to-cart-button"
          )
            options.push(
              <ButtonColor {...styleProps} target="props" />,
              <ButtonTextColor {...styleProps} target="props" />
            );
          break;
        // Table Elements
        case "product-image":
        case "product-name":
        case "in-stock-amount":
        case "sku":
        case "price":
        case "short-description":
        case "variation-dropdown":
        case "quantity-input":
        case "product-meta":
          options.push(
            <Alignment
              {...styleProps}
              target="props"
              label={alignmentStrings?.cell_alignment}
              section="formTable"
            />
          );
          break;

        case "add-to-cart-button":
          options.push(
            <Alignment
              {...styleProps}
              target="props"
              label={alignmentStrings?.cell_alignment}
              section="formTable"
            />,
            <Width {...styleProps} target="props" />,
            <FontSize {...styleProps} target="props" />,
            <Padding {...styleProps} target="props" />,
            <ButtonColor {...styleProps} target="props" />,
            <ButtonTextColor {...styleProps} target="props" />
          );
          break;
        default:
      }

      if (options.length > 0)
        return options.map((component: any, key: number) =>
          React.cloneElement(component, { key })
        );
      else return <>{showStylesStrings?.no_styles}</>;
    }
  };

  return <>{displayStyles()}</>;
};

const mapStateToProps = (store: any) => ({
  showStylesStrings: store.i18n.backend.styling_and_option_controls.show_styles,
  alignmentStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.alignment,
  widthStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.width,
});

export default connect(mapStateToProps)(ShowStyles);
