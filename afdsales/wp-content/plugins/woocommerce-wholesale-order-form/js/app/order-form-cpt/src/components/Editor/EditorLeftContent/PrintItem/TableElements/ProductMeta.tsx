import {
  useRef,
  MutableRefObject,
  useEffect,
  useState,
  createRef,
} from "react";
import { CaretUpOutlined, CaretDownOutlined } from "@ant-design/icons";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

// Helper
import { htmlDecode } from "helpers/htmlDecode";

export interface IMetaData {
  key: React.Key;
  name: string;
  meta_key: string;
}

const DisplayMetaData = (props: any) => {
  const { orderForm, product } = props;
  const metaKeys = orderForm?.settingsData?.meta_data;

  if (typeof metaKeys === "undefined" || metaKeys.length <= 0) return <></>;

  let elements: React.ReactNode[] = [];

  metaKeys.forEach((md: IMetaData, i: number) => {
    let indexes: number[] = [];
    product.meta_data.forEach((md2: any, j: number) => {
      if (md2.key === md.meta_key) {
        indexes.push(j);
      }
    });

    if (indexes.length > 0) {
      elements.push(
        <span key={i} style={{ display: "block" }}>
          <b>{md.name}</b>
          {`: ${indexes.map(
            (index: number) => " " + htmlDecode(product.meta_data[index].value)
          )}`}
        </span>
      );
    }
  });

  return <>{elements}</>;
};

const ProductMeta = (props: any) => {
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
    productMetaStrings,
  } = props;

  const { column_heading } = productMetaStrings;

  const [columnRefs, setColumnRefs] = useState([]);
  const [columnRows, setColumnRows] = useState([]);

  // Re-render when rows are updated
  const [, setForceRender] = useState([]);

  let sortable = getPropValue({
    properties,
    prop: "sortable",
  });

  if (sortable === undefined) sortable = true;

  const headerText =
    getPropValue({ properties, prop: "columnHeading" }) || column_heading;

  const nameAlignment =
    getPropValue({
      properties,
      prop: "justifyContent",
    }) || "center";

  const nameStyle = {
    display: "flex",
    justifyContent: nameAlignment,
    textAlign:
      nameAlignment === "flex-start"
        ? ("left" as const)
        : nameAlignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const targetRef = useRef(null) as unknown as MutableRefObject<HTMLDivElement>;

  useEffect(() => {
    if (
      targetRef.current.offsetHeight > 0 &&
      headerHeight < targetRef.current.offsetHeight
    )
      setHeaderHeight(targetRef.current.offsetHeight);
  }, [targetRef, headerText]);

  useEffect(() => {
    if (products.products.length > 0) {
      setColumnRefs(products.products.map((p: any, i: any) => createRef()));
    }
  }, [products.products]);

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
  }, [columnRows]);

  return (
    <>
      <div
        className="heading"
        style={{
          ...nameStyle,
          height: headerHeight > 0 ? headerHeight + "px" : "",
        }}
        ref={targetRef}
      >
        {headerText}
        {sortable ? (
          <span style={{ width: "14px", marginLeft: "4px" }}>
            <CaretUpOutlined
              style={{ float: "left", height: "8px", color: "#bfbfbf" }}
            />
            <CaretDownOutlined
              style={{ float: "left", height: "8px", color: "#bfbfbf" }}
            />
          </span>
        ) : (
          ""
        )}
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
            data-productid={d.id}
            className={`row ${
              i % 2 === 0 ? "odd" : "even"
            } ${selected} row${i}`}
            style={{
              ...nameStyle,
              ...heightCSS,
            }}
            onMouseEnter={(e: any) => onMouseEnter({ e, setHoveredRow })}
            onMouseLeave={(e: any) => onMouseLeave({ e, setHoveredRow })}
            ref={columnRefs[i]}
          >
            <div>
              <DisplayMetaData orderForm={props.orderForm} product={d} />
            </div>
          </div>
        );
      })}
    </>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  productMetaStrings: store.i18n.backend.table_elements.product_meta,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({}, dispatch),
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductMeta);
