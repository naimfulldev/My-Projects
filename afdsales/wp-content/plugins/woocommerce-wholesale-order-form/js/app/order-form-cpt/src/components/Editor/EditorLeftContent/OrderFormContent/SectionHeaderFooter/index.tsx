import DisplayItem from "./DisplayItem";
import { Droppable, Draggable } from "react-beautiful-dnd";
import AddNewSection from "./AddNewSection";
import ManageRow from "../../ManageRow";
import { getPropValue } from "helpers/getPropValue";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { stylingActions, dragAndDropActions } from "store/actions";
import SectionSettings from "../SectionSettings";

const { setDndData } = dragAndDropActions;
const { setShowStyling, setStyles } = stylingActions;

const SectionHeaderFooter = (props: any) => {
  const {
    data,
    sectionId,
    actions,
    styling,
    products,
    readableStyles,
    pagination,
    orderForm,
  } = props;
  const { setShowStyling, setStyles, setDndData } = actions;
  const dataRows = data["editorArea"][sectionId];

  const sectionStyles =
    styling.styles[sectionId] !== undefined ? styling.styles[sectionId] : {};

  let showHeader = getPropValue({
    styling,
    id: sectionId,
    target: "props",
    style: "showFormHeader",
    extra: "",
  });

  let showFooter = getPropValue({
    styling,
    id: sectionId,
    target: "props",
    style: "showFormFooter",
    extra: "",
  });

  if (showHeader === null || showHeader === undefined) showHeader = true;
  if (showFooter === null || showFooter === undefined) showFooter = true;

  let sectionStyle = {};
  if (sectionId === "formHeader" && showHeader === false) {
    sectionStyle = { display: "none" };
  } else if (sectionId === "formFooter" && showFooter === false) {
    sectionStyle = { display: "none" };
  }

  // If there are no elements inside header and footer show "Add New Section" option
  if (dataRows["rows"].length === 0)
    return <AddNewSection sectionId={sectionId} hasRows={false} />;
  else
    return (
      <Droppable
        droppableId={sectionId}
        // direction="horizontal"
        type={sectionId}
      >
        {(provided) => (
          <div
            className={`formHeaderFooterContainer`}
            {...provided.droppableProps}
            ref={provided.innerRef}
            style={{
              position: "relative",
              border: "1px solid #eee",
              background: "#FAFAFA",
              paddingTop: 20,
              paddingRight: 20,
              paddingBottom: 20,
              paddingLeft: 20,
              // display: "grid",
              // gridGap: "10px",
              ...readableStyles({ styles: sectionStyles["box"] }),
            }}
          >
            <SectionSettings
              sectionId={sectionId}
              hasItems={dataRows["rows"].length > 0 ? true : false}
              setShowStyling={setShowStyling}
            />
            {/* Show Hide Header / Footer */}
            <div className={sectionId} style={{ ...sectionStyle }}>
              {Object.keys(dataRows["rows"]).map((row: any, index: any) => {
                const rowId = dataRows["rows"][row]["rowId"];
                const rowStyles =
                  styling.styles[rowId] !== undefined
                    ? styling.styles[rowId]
                    : {};
                const columns = dataRows["rows"][row]["columns"];

                const rowProps = {
                  sectionId,
                  row,
                  data,
                  setData: setDndData,
                  setShowStyling,
                  styling,
                  setStyles,
                };

                const rowEditMode =
                  styling.show && styling.item.id === rowId ? true : false;

                return (
                  <div key={index}>
                    <Draggable
                      draggableId={`${sectionId}-${row}`}
                      index={index}
                      key={`${sectionId}-${row}`}
                    >
                      {(provided, snapshot) => (
                        <div
                          {...provided.draggableProps}
                          ref={provided.innerRef}
                          key={index}
                          className={`row ${rowId}${
                            rowEditMode ? " edit-mode" : ""
                          }`}
                          data-rowid={rowId}
                          style={{
                            border: "1px dashed #ccc",
                            minHeight: "40px",
                            display: "inline-block",
                            marginBottom: "10px",
                            ...readableStyles({ styles: rowStyles["box"] }),
                            ...provided.draggableProps.style,
                          }}
                        >
                          <ManageRow
                            {...rowProps}
                            provided={provided}
                            snapshot={snapshot}
                          />

                          <Droppable
                            droppableId={`${sectionId}-${row}`}
                            direction="horizontal"
                            type={`${sectionId}-${row}`}
                          >
                            {(provided, snapshot) => (
                              <div
                                {...provided.droppableProps}
                                ref={provided.innerRef}
                                style={{
                                  display: "flex",
                                  flexDirection: "row",
                                  margin: "0px 10px 10px 10px",
                                }}
                              >
                                {Object.keys(columns).map(
                                  (col: any, index: any) => {
                                    const colId = columns[col]["colId"];
                                    const colStyles =
                                      styling.styles[colId] !== undefined
                                        ? styling.styles[colId]
                                        : {};
                                    const items =
                                      dataRows["rows"][row]["columns"][col][
                                        "itemIds"
                                      ];
                                    const columnEditMode =
                                      styling.show && styling.item.id === colId
                                        ? true
                                        : false;

                                    return (
                                      <Draggable
                                        draggableId={`${sectionId}-${row}-${col}`}
                                        index={index}
                                        key={`${sectionId}-${row}-${col}`}
                                      >
                                        {(provided, snapshot) => {
                                          const itemProvided = provided;
                                          const itemSnapshot = snapshot;

                                          const readableColStyles =
                                            readableStyles({
                                              styles: colStyles["box"],
                                            });

                                          return (
                                            <div
                                              {...provided.draggableProps}
                                              className={`col ${col} ${
                                                snapshot.isDragging
                                                  ? "dragging"
                                                  : ""
                                              }`}
                                              ref={provided.innerRef}
                                              key={index}
                                              style={{
                                                minWidth: "40px",
                                                minHeight: "40px",
                                                // flex: hasCustomWidth ? "none" : 1,
                                                // margin: '5px',
                                                padding: "4px",
                                                // backgroundColor: snapshot.isDragging
                                                //   ? "#263B4A"
                                                //   : "inherit",
                                                ...readableColStyles,
                                                ...provided.draggableProps
                                                  .style,
                                              }}
                                            >
                                              <span
                                                style={{ display: "none" }}
                                                {...provided.dragHandleProps}
                                              ></span>
                                              <Droppable
                                                droppableId={`${sectionId}-${row}-${col}-item`}
                                                // direction="horizontal"
                                                // type={`${row}${col}`}
                                                type={
                                                  sectionId !== "formTable"
                                                    ? "HEADER-FOOTER"
                                                    : "DEFAULT"
                                                }
                                                isDropDisabled={
                                                  items.length > 0
                                                    ? true
                                                    : false
                                                }
                                              >
                                                {(provided, snapshot) => {
                                                  const itemProps = {
                                                    snapshot,
                                                    items,
                                                    data,
                                                    sectionId,
                                                    row,
                                                    col,
                                                    setStyles,
                                                    styling,
                                                    colId,
                                                    setDndData,
                                                    setShowStyling,
                                                    readableStyles,
                                                    colStyles,
                                                    products,
                                                    pagination,
                                                    orderForm,
                                                    itemProvided,
                                                    itemSnapshot,
                                                    provided,
                                                  };

                                                  return (
                                                    <div
                                                      className={`drop-item ${
                                                        items.length > 0
                                                          ? "has-items"
                                                          : "no-item"
                                                      }${
                                                        columnEditMode
                                                          ? " edit-mode"
                                                          : ""
                                                      } container-${colId}`}
                                                      {...provided.droppableProps}
                                                      ref={provided.innerRef}
                                                      style={{
                                                        border:
                                                          "1px dashed #ccc",
                                                        background:
                                                          snapshot.isDraggingOver
                                                            ? "lightblue"
                                                            : "inherit",
                                                        ...readableColStyles,
                                                        // ...readableStyles({
                                                        //   styles: colStyles["box"],
                                                        // }),
                                                      }}
                                                    >
                                                      <DisplayItem
                                                        {...itemProps}
                                                      />
                                                    </div>
                                                  );
                                                }}
                                              </Droppable>
                                            </div>
                                          );
                                        }}
                                      </Draggable>
                                    );
                                  }
                                )}
                                {provided.placeholder}
                              </div>
                            )}
                          </Droppable>
                        </div>
                      )}
                    </Draggable>
                    <div style={{ clear: "both" }} />
                  </div>
                );
              })}
            </div>
            <div style={{ ...sectionStyle }}>
              <AddNewSection sectionId={sectionId} hasRows={true} />
            </div>
            {provided.placeholder}
          </div>
        )}
      </Droppable>
    );
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  data: store.dragAndDrop,
  styling: store.styling,
  products: store.products,
  pagination: store.pagination,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setShowStyling,
      setStyles,
      setDndData,
    },
    dispatch
  ),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(SectionHeaderFooter);
