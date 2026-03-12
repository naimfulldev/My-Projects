import { useEffect, useMemo, useState } from "react";
import { Droppable, Draggable } from "react-beautiful-dnd";
import ManageColumn from "../../ManageColumn";
import PrintItem from "../../PrintItem";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import {
  stylingActions,
  productActions,
  dragAndDropActions,
} from "store/actions";
import SectionSettings from "../SectionSettings";
import TableSkeleton from "../TableSkeleton";

const { setShowStyling } = stylingActions;
const { fetchCategories } = productActions;
const { setDndData } = dragAndDropActions;

const SectionTable = (props: any) => {
  const {
    sectionId,
    data,
    styling,
    products,
    actions,
    readableStyles,
    sectionTableStrings,
  } = props;

  const { drop_table_columns_here } = sectionTableStrings;

  const { setShowStyling, fetchCategories, setDndData } = actions;

  const [hoveredRow, setHoveredRow] = useState("");
  const [headerHeight, setHeaderHeight] = useState(0);
  const [widthType, setWidthType] = useState("");

  const sectionStyles = useMemo(
    () =>
      styling.styles[sectionId] !== undefined ? styling.styles[sectionId] : {},
    [sectionId, styling.styles]
  );

  useEffect(() => {
    try {
      // Get width type. auto|percentage|px
      let type = "";
      if (
        typeof sectionStyles["box"] !== "undefined" &&
        typeof sectionStyles["box"]["width"] !== "undefined" &&
        typeof sectionStyles["box"]["width"]["type"] !== "undefined"
      ) {
        type = sectionStyles["box"]["width"]["type"];
        setWidthType(type);
      }
    } catch (e) {
      console.log(e);
    }
  }, [sectionStyles, products]);

  useEffect(() => {
    fetchCategories({
      categories: [],
    });
  }, [fetchCategories]);

  const getTablePaddingAndSpacing = (props: any) => {
    const { styling } = props;
    const sectionStyles =
      styling.styles["formTable"] !== undefined
        ? styling.styles["formTable"]
        : {};
    let tableSpacing = {
      cellSpacing: 0,
      cellPadding: 0,
    };
    if (sectionStyles["element"] !== undefined) {
      if (sectionStyles["element"]["cellSpacing"] !== undefined) {
        tableSpacing.cellSpacing = sectionStyles["element"]["cellSpacing"];
      }
      if (sectionStyles["element"]["cellPadding"] !== undefined) {
        tableSpacing.cellPadding = sectionStyles["element"]["cellPadding"];
      }
    }
  };

  if (
    products.fetchingProducts &&
    data["editorArea"][sectionId]["itemIds"].length > 0
  ) {
    return (
      <TableSkeleton
        {...props}
        itemIds={data["editorArea"][sectionId]["itemIds"]}
      />
    );
  } else
    return (
      <Droppable
        direction={"horizontal"}
        droppableId={sectionId}
        key={sectionId}
      >
        {(provided, snapshot) => {
          return (
            <div
              className="columns-wrapper"
              style={{
                border:
                  data["editorArea"][sectionId]["itemIds"].length <= 0
                    ? "none"
                    : "1px solid rgb(238, 238, 238)",
              }}
            >
              <div
                className={`columns ${sectionId} ${sectionId}Container`}
                {...provided.droppableProps}
                ref={provided.innerRef}
                style={{
                  justifyContent:
                    data["editorArea"][sectionId]["itemIds"].length <= 0
                      ? "space-around"
                      : "",
                  position: "relative",
                  background: "#FAFAFA",
                  padding: 4,
                  minHeight:
                    data["editorArea"][sectionId]["itemIds"].length <= 0
                      ? "250px"
                      : "",
                  lineHeight:
                    data["editorArea"][sectionId]["itemIds"].length <= 0 &&
                    !snapshot.isDraggingOver
                      ? "250px"
                      : "",
                  ...readableStyles({ styles: sectionStyles["box"] }),
                }}
              >
                <SectionSettings
                  sectionId={sectionId}
                  hasItems={
                    data["editorArea"][sectionId]["itemIds"].length > 0
                      ? true
                      : false
                  }
                  setShowStyling={setShowStyling}
                />
                {data["editorArea"][sectionId]["itemIds"].length <= 0 &&
                !snapshot.isDraggingOver
                  ? drop_table_columns_here
                  : ""}
                {data["editorArea"][sectionId]["itemIds"].map(
                  (itemId: any, index: any) => {
                    const colStyles =
                      styling.styles[itemId] !== undefined
                        ? styling.styles[itemId]
                        : {};
                    const colId = data["editorArea"][sectionId]["colId"];
                    const item = data["items"][itemId];

                    const editMode =
                      styling.show && styling.item.id === itemId ? true : false;

                    if (typeof item === "undefined")
                      return <div key={index}></div>;
                    else
                      return (
                        <Draggable
                          key={item.id}
                          draggableId={item.id}
                          index={index}
                        >
                          {(provided, snapshot) => {
                            const colProps = {
                              colId,
                              sectionId,
                              item,
                              data,
                              setDndData,
                              setShowStyling,
                            };

                            let flexValue: any = { flex: "initial" };
                            if (
                              widthType === "" ||
                              widthType === "full-width"
                            ) {
                              flexValue = {
                                flex: [
                                  "short-description",
                                  "product-name",
                                ].includes(item.id)
                                  ? "initial"
                                  : 1,
                              };
                            }

                            return (
                              <div
                                className={`table-item ${
                                  snapshot.isDragging ? "dragging" : "dropped"
                                } ${editMode ? "edit-mode" : ""}`}
                                ref={provided.innerRef}
                                {...provided.draggableProps}
                                style={{
                                  padding:
                                    sectionId === "formTable" ? "0px" : 8,
                                  marginBottom:
                                    sectionId === "formTable" ? "0px" : "5px",
                                  ...flexValue,
                                  ...provided.draggableProps.style,
                                }}
                              >
                                <ManageColumn
                                  {...colProps}
                                  provided={provided}
                                  snapshot={snapshot}
                                />
                                <PrintItem
                                  item={item}
                                  products={products}
                                  properties={colStyles["props"]}
                                  boxStyles={colStyles["box"]}
                                  elementStyles={colStyles["element"]}
                                  hoveredRow={hoveredRow}
                                  setHoveredRow={setHoveredRow}
                                  headerHeight={headerHeight}
                                  setHeaderHeight={setHeaderHeight}
                                  tableSpacing={getTablePaddingAndSpacing({
                                    styling,
                                  })}
                                />
                              </div>
                            );
                          }}
                        </Draggable>
                      );
                  }
                )}
                {provided.placeholder}
              </div>
            </div>
          );
        }}
      </Droppable>
    );
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  data: store.dragAndDrop,
  styling: store.styling,
  products: store.products,
  sectionTableStrings: store.i18n.backend.order_form_content.section_table,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setShowStyling,
      fetchCategories,
      setDndData,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(SectionTable);
