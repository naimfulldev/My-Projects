import {
  useRef,
  useLayoutEffect,
  MutableRefObject,
  useEffect,
  createRef,
  useState,
} from "react";

import StandardVariationsDropdown from "./shared/StandardVariationsDropdown";
import ComboVariationsDropdown from "./shared/ComboVariationsDropdown";

// Redux
import { connect } from "react-redux";
import { bindActionCreators, Dispatch } from "redux";

import { productActions } from "store/actions";

const { loadMoreVariations } = productActions;

const VariationDropdown = (props: any) => {
  const {
    getPropValue,
    properties,
    orderForm,
    products,
    hoveredRow,
    setHoveredRow,
    onMouseEnter,
    onMouseLeave,
    headerHeight,
    setHeaderHeight,
    actions,
    variationDropdownStrings,
  } = props;

  const { column_heading } = variationDropdownStrings;

  const { loadMoreVariations } = actions;
  const [variationSelector, setVariationSelector] = useState(
    getPropValue({
      properties,
      prop: "variationSelectorStyle",
    }) || "combo"
  );

  const [columnRefs, setColumnRefs] = useState([]);
  const [columnRows, setColumnRows] = useState([]);

  // Re-render when rows are updated
  const [, setForceRender] = useState([]);

  const headerText =
    getPropValue({ properties, prop: "columnHeading" }) || column_heading;

  const variationAlignment =
    getPropValue({
      properties,
      prop: "justifyContent",
    }) || "center";

  const variationStyleHeader = {
    display: "flex",
    justifyContent: variationAlignment,
    textAlign:
      variationAlignment === "flex-start"
        ? ("left" as const)
        : variationAlignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const variationStyleRows = {
    verticalAlign: "middle",
    display: "grid",
    justifyContent: variationAlignment,
    textAlign:
      variationAlignment === "flex-start"
        ? ("left" as const)
        : variationAlignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const targetRef = useRef(null) as unknown as MutableRefObject<HTMLDivElement>;

  useLayoutEffect(() => {
    if (
      targetRef.current.offsetHeight > 0 &&
      headerHeight < targetRef.current.offsetHeight
    )
      setHeaderHeight(targetRef.current.offsetHeight);
  }, [targetRef, headerText]);

  useLayoutEffect(() => {
    if (products.products.length > 0) {
      setColumnRefs(products.products.map((p: any, i: any) => createRef()));
    }
  }, [products.variations, variationSelector]);

  useEffect(() => {
    if (columnRefs.length && products.products.length > 0) {
      let tempRowsHeight: any = [];

      columnRefs.forEach((ref: any, i: any) => {
        let temp = null;

        if (ref.current !== null && ref.current.style !== null) {
          temp = ref.current.style.height;
          ref.current.style.height = "";
        }

        if (ref.current) {
          tempRowsHeight[i] = ref.current.offsetHeight;
        }

        if (temp !== null) ref.current.style.height = temp;
      });

      setColumnRows(tempRowsHeight);
    }
  }, [columnRefs, products.products, variationSelector]);

  useEffect(() => {
    if (columnRows.length > 0) {
      let rowsHeight: any = localStorage.getItem("rowsHeight") || [];

      if (typeof rowsHeight === "string") {
        rowsHeight = JSON.parse(rowsHeight);
      }

      if (rowsHeight.length === 0) {
        localStorage.setItem("rowsHeight", JSON.stringify(columnRows));
      } else {
        let newCellsHeight: any = [];

        columnRows.forEach((height: any, i: any) => {
          if (typeof rowsHeight[i] !== "undefined" && height > rowsHeight[i]) {
            newCellsHeight[i] = height;
          } else if (typeof rowsHeight[i] === "undefined") {
            newCellsHeight[i] = height;
          } else {
            newCellsHeight[i] = rowsHeight[i];
          }
        });
        localStorage.setItem("rowsHeight", JSON.stringify(newCellsHeight));
      }
      setForceRender(rowsHeight);
    }
  }, [columnRows, variationSelector]);

  useEffect(() => {
    setVariationSelector(
      getPropValue({
        properties,
        prop: "variationSelectorStyle",
      })
    );
  }, [properties]);

  return (
    <>
      <div
        className="heading"
        style={{
          ...variationStyleHeader,
          height: headerHeight > 0 ? headerHeight + "px" : "",
        }}
        ref={targetRef}
      >
        {headerText}
      </div>
      {products.products.map((d: any, i: any) => {
        let selected = d.id === parseInt(hoveredRow) ? "hovered" : "";

        let heightCSS: any = [];
        let rowsHeight: any = localStorage.getItem("rowsHeight") || [];

        if (typeof rowsHeight === "string") {
          rowsHeight = JSON.parse(rowsHeight);
        }
        if (typeof rowsHeight[i] !== "undefined") {
          heightCSS = {
            height: rowsHeight[i] + "px",
          };
        }

        return (
          <div
            key={i}
            className={`row ${
              i % 2 === 0 ? "odd" : "even"
            } ${selected} row${i} variation-dropdown-row`}
            style={{
              ...variationStyleRows,
              ...heightCSS,
            }}
            data-productid={d.id}
            onMouseEnter={(e: any) => onMouseEnter({ e, setHoveredRow })}
            onMouseLeave={(e: any) => onMouseLeave({ e, setHoveredRow })}
            ref={columnRefs[i]}
          >
            {variationSelector === null || variationSelector === "combo" ? (
              <ComboVariationsDropdown
                orderForm={orderForm}
                products={products}
                product={d}
                variations={products.variations}
                loadMoreVariations={loadMoreVariations}
              />
            ) : (
              <StandardVariationsDropdown
                roducts={products}
                product={d}
                variations={products.variations}
              />
            )}
          </div>
        );
      })}
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  products: store.products,
  variationDropdownStrings:
    store.i18n.backend.table_elements.variation_dropdown,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      loadMoreVariations,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(VariationDropdown);
