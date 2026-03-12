import {
  MutableRefObject,
  useRef,
  useEffect,
  useState,
  createRef,
} from "react";
import { Button } from "antd";

// Helper
import { getRowsHeight } from "helpers/getRowsHeight";

import { connect } from "react-redux";

const AddToCartButton = (props: any) => {
  const {
    getPropValue,
    properties,
    products,
    hoveredRow,
    setHoveredRow,
    onMouseEnter,
    onMouseLeave,
    headerHeight,
    setHeaderHeight,
    useStyleValue,
    addToCartButtonStrings,
  } = props;

  const { column_heading, button_text } = addToCartButtonStrings;

  const [columnRefs, setColumnRefs] = useState([]);
  const [columnRows, setColumnRows] = useState([]);

  // Re-render when rows are updated
  const [, setForceRender] = useState([]);

  const headerText =
    getPropValue({ properties, prop: "columnHeading" }) || column_heading;

  let fontSize = useStyleValue(
    getPropValue({
      properties,
      prop: "fontSize",
    }) || ""
  );

  let width = useStyleValue(
    getPropValue({
      properties,
      prop: "width",
    }) || ""
  );

  const addToCartAlignment =
    getPropValue({
      properties,
      prop: "justifyContent",
    }) || "center";

  const buttonColor =
    getPropValue({
      properties,
      prop: "buttonColor",
    }) || "";

  const buttonTextColor =
    getPropValue({
      properties,
      prop: "buttonTextColor",
    }) || "";

  // CSS
  const addToCartStyle = {
    display: "flex",
    justifyContent: addToCartAlignment,
    textAlign:
      addToCartAlignment === "flex-start"
        ? ("left" as const)
        : addToCartAlignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const addToCartElementStyle = {
    width,
    fontSize: fontSize === "auto" ? "inherit" : fontSize,
    border: buttonColor,
    background: buttonColor,
    color: buttonTextColor,
    paddingTop:
      getPropValue({
        properties,
        prop: "paddingTop",
      }) || 0,
    paddingRight:
      getPropValue({
        properties,
        prop: "paddingRight",
      }) || 0,
    paddingBottom:
      getPropValue({
        properties,
        prop: "paddingBottom",
      }) || 0,
    paddingLeft:
      getPropValue({
        properties,
        prop: "paddingLeft",
      }) || 0,
  };

  const targetRef = useRef(null) as unknown as MutableRefObject<HTMLDivElement>;

  useEffect(() => {
    if (
      targetRef.current.offsetHeight > 0 &&
      headerHeight < targetRef.current.offsetHeight
    )
      setHeaderHeight(targetRef.current.offsetHeight);
  }, [targetRef, headerText, headerHeight, setHeaderHeight]);

  if (
    addToCartElementStyle.paddingTop === 0 &&
    addToCartElementStyle.paddingRight === 0 &&
    addToCartElementStyle.paddingBottom === 0 &&
    addToCartElementStyle.paddingLeft === 0
  ) {
    delete addToCartElementStyle.paddingTop;
    delete addToCartElementStyle.paddingRight;
    delete addToCartElementStyle.paddingBottom;
    delete addToCartElementStyle.paddingLeft;
  }

  useEffect(() => {
    if (products.products.length > 0) {
      setColumnRefs(products.products.map((p: any, i: any) => createRef()));
    }
  }, [products.products]);

  useEffect(() => {
    if (columnRefs.length && products.products.length > 0) {
      let tempRowsHeight: any = [];

      columnRefs.map((ref: any, i: any) => {
        let temp = null;

        if (ref.current !== null && ref.current.style !== null) {
          temp = ref.current.style.height;
          ref.current.style.height = "";
        }

        if (ref.current) {
          tempRowsHeight[i] = ref.current.offsetHeight;
        }

        if (temp !== null) ref.current.style.height = temp;

        return false;
      });

      setColumnRows(tempRowsHeight);
    }
  }, [columnRefs, products.products]);

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

        columnRows.map((height: any, i: any) => {
          if (typeof rowsHeight[i] !== "undefined" && height > rowsHeight[i]) {
            newCellsHeight[i] = height;
          } else if (typeof rowsHeight[i] === "undefined") {
            newCellsHeight[i] = height;
          } else {
            newCellsHeight[i] = rowsHeight[i];
          }
          return false;
        });
        localStorage.setItem("rowsHeight", JSON.stringify(newCellsHeight));
      }
      setForceRender(rowsHeight);
    }
  }, [columnRows]);

  return (
    <>
      <div
        className="heading"
        style={{
          ...addToCartStyle,
          height: headerHeight > 0 ? headerHeight + "px" : "",
        }}
        ref={targetRef}
      >
        {headerText}
      </div>
      {products.products.map((d: any, i: any) => {
        let selected = d.id === parseInt(hoveredRow) ? "hovered" : "";
        let heightCSS: any = getRowsHeight({ i });
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
            } ${selected} row${i}`}
            style={{
              ...addToCartStyle,
              ...heightCSS,
            }}
            data-productid={d.id}
            onMouseEnter={(e: any) => onMouseEnter({ e, setHoveredRow })}
            onMouseLeave={(e: any) => onMouseLeave({ e, setHoveredRow })}
            ref={columnRefs[i]}
          >
            <Button
              size="large"
              type="primary"
              style={{ ...addToCartElementStyle }}
            >
              {getPropValue({ properties, prop: "buttonText" }) || button_text}
            </Button>
          </div>
        );
      })}
    </>
  );
};

const mapStateToProps = (store: any) => ({
  addToCartButtonStrings: store.i18n.backend.table_elements.add_to_cart_button,
});

export default connect(mapStateToProps)(AddToCartButton);
