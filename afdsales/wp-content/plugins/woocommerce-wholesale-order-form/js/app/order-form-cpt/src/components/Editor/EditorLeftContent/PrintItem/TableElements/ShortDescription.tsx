import {
  useRef,
  MutableRefObject,
  useEffect,
  useState,
  createRef,
} from "react";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import truncateStrings from "helpers/truncateStrings";

const ShortDescription = (props: any) => {
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
    shortDescriptionStrings,
  } = props;

  const { column_heading } = shortDescriptionStrings;

  const [columnRefs, setColumnRefs] = useState([]);
  const [columnRows, setColumnRows] = useState([]);

  // Re-render when rows are updated
  const [, setForceRender] = useState([]);

  const headerText =
    getPropValue({ properties, prop: "columnHeading" }) || column_heading;

  const descriptionLength =
    getPropValue({ properties, prop: "maxCharacters" }) || 200;

  const descriptionAlignment =
    getPropValue({
      properties,
      prop: "justifyContent",
    }) || "center";

  const descriptionStyleHeader = {
    display: "flex",
    justifyContent: descriptionAlignment,
    textAlign:
      descriptionAlignment === "flex-start"
        ? ("left" as const)
        : descriptionAlignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const descriptionStyleRows = {
    // display: "flex",
    verticalAlign: "middle",
    textAlign:
      descriptionAlignment === "flex-start"
        ? ("left" as const)
        : descriptionAlignment === "flex-end"
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
          ...descriptionStyleHeader,
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

        let description = d.short_description;

        if (parseInt(descriptionLength) > 0)
          description = truncateStrings(
            description,
            parseInt(descriptionLength),
            "..."
          );

        return (
          <div
            key={i}
            className={`row ${
              i % 2 === 0 ? "odd" : "even"
            } ${selected} row${i}`}
            style={{
              ...descriptionStyleRows,
              ...heightCSS,
            }}
            data-productid={d.id}
            onMouseEnter={(e: any) => onMouseEnter({ e, setHoveredRow })}
            onMouseLeave={(e: any) => onMouseLeave({ e, setHoveredRow })}
            dangerouslySetInnerHTML={{ __html: description || "" }}
            ref={columnRefs[i]}
          />
        );
      })}
    </>
  );
};

const mapStateToProps = (store: any) => ({
  shortDescriptionStrings: store.i18n.backend.table_elements.short_description,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({}, dispatch),
});

export default connect(mapStateToProps, mapDispatchToProps)(ShortDescription);
