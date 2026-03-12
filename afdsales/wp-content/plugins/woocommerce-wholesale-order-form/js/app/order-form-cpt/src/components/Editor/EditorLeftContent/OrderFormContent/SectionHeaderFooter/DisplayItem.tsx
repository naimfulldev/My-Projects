import { Draggable } from "react-beautiful-dnd";
import ManageColumn from "../../ManageColumn";
import PrintItem from "../../PrintItem";

import { updateStyling } from "helpers/updateStyling";

const DisplayItem = (props: any) => {
  const {
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
  } = props;

  const onResize = (e: any, direction: any, ref: any, d: any) => {
    const parentContainer: any = document.querySelector(".container-" + colId);
    if (
      parentContainer !== null &&
      parentContainer.style !== null &&
      parentContainer.style.width !== "auto"
    ) {
      parentContainer.style.width = "auto";
    }
  };

  const onResizeStop = (e: any, direction: any, ref: any, d: any) => {
    const parentContainer: any = document.querySelector(".container-" + colId);

    if (parentContainer !== undefined) {
      updateStyling({
        setStyles,
        styling,
        id: colId,
        target: "box",
        toUpdate: {
          width: {
            value: ref.offsetWidth,
            type: "pixels",
          },
        },
      });
    }
  };

  let textAlignValue: string = "left";
  if (
    colStyles["box"] !== undefined &&
    colStyles["box"]["justifyContent"] !== undefined
  ) {
    let justifyContent = colStyles["box"]["justifyContent"];
    switch (justifyContent) {
      case "flex-start":
        textAlignValue = "left";
        break;
      case "flex-end":
        textAlignValue = "right";
        break;
      case "center":
        textAlignValue = "center";
        break;
      default:
        break;
    }
  }
  let textAlignCSS: any = {
    textAlign: textAlignValue,
  };

  return (
    <>
      {!snapshot.isDraggingOver && items.length === 0 ? "" : ""}
      {items.map((itemKey: any, index: any) => {
        const item = data["items"][itemKey];

        return (
          <Draggable
            draggableId={`${sectionId}-${row}-${col}-item`}
            index={index}
            key={`${sectionId}-${row}-${col}-item`}
            isDragDisabled={true}
          >
            {(provided, snapshot) => {
              const colProps = {
                setStyles,
                styling,
                colId,
                sectionId,
                item,
                data,
                setDndData,
                area: `${sectionId}-${row}-${col}`,
                setShowStyling,
              };
              const printItemProps = {
                item,
                styles: readableStyles({
                  styles: colStyles["element"],
                }),
                properties: colStyles["props"],
                products,
                pagination,
                orderForm,
              };

              return (
                <>
                  <ManageColumn
                    {...colProps}
                    provided={itemProvided}
                    snapshot={itemSnapshot}
                  />
                  <div
                    {...provided.dragHandleProps}
                    {...provided.draggableProps}
                    className={`column ${col} item ${
                      snapshot.isDragging ? " dragging" : ""
                    }`}
                    ref={provided.innerRef}
                    style={{
                      ...textAlignCSS,
                      backgroundColor: snapshot.isDragging
                        ? "#263B4A"
                        : "inherit",
                      padding: "10px",
                      ...provided.draggableProps.style,
                    }}
                  >
                    <PrintItem {...printItemProps} />
                  </div>
                </>
              );
            }}
          </Draggable>
        );
      })}
      {provided.placeholder}
    </>
  );
};
export default DisplayItem;
