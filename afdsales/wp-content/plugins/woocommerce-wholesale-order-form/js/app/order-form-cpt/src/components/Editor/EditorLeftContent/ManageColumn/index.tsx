import { FormOutlined, DeleteOutlined, DragOutlined } from "@ant-design/icons";
import { defaults } from "store/reducers/dragAndDropReducer";
import { Tooltip, Popconfirm } from "antd";

// Redux
import { connect } from "react-redux";

// Handles Deleting Item in Row Columns
const ManageColumn = (props: any) => {
  const {
    sectionId,
    item,
    data,
    setDndData,
    area,
    setShowStyling,
    styling,
    setStyles,
    provided,
    snapshot,
    manageColumnStrings,
    dragAndDropLabelStrings,
  } = props;

  const {
    settings_for,
    column_str,
    item_str,
    reorder_column,
    reorder_item,
    delete_msg,
    yes_str,
    no_str,
  } = manageColumnStrings;

  const dndData: any = defaults;

  const getColId = (column: string) => {
    if (area !== undefined) {
      let destElement = area.split("-");

      // deleting from formHeader and footerFooter
      if (column !== "formTable")
        return data["editorArea"][column]["rows"][destElement[1]]["columns"][
          destElement[2]
        ]["colId"];
      return null;
    } else return data["editorArea"][column]["id"];
  };

  const findOriginalLocation = (id: number) => {
    for (let column of Object.keys(dndData["formElements"])) {
      const index = dndData["formElements"][column]["itemIds"].indexOf(id);
      if (index >= 0) return { index, column };
    }
    return null;
  };

  const deletingItem = (id: number, column: string) => {
    let itemIds: any = [];
    let rowId: number = 0;
    let colId: number = 0;
    let destElement: any[] = [];

    if (area !== undefined) {
      destElement = area.split("-");
      rowId = parseInt(destElement[1]);
      colId = parseInt(destElement[2]);

      // deleting from formHeader and footerFooter
      if (column !== "formTable")
        itemIds =
          data["editorArea"][column]["rows"][destElement[1]]["columns"][
            destElement[2]
          ]["itemIds"];
    } else itemIds = data["editorArea"][column]["itemIds"]; // deleting from formTable

    const index = itemIds.indexOf(id);

    if (index >= 0) {
      const originalLocation: any = findOriginalLocation(id);

      // Remove from Editor
      itemIds.splice(index, 1);

      // Return to Form Elements
      const dest = data["formElements"][originalLocation.column]["itemIds"];
      dest.splice(originalLocation.index, 0, id);

      if (column !== "formTable") {
        // Handle updating 2x nested array of objects
        let updated = data["editorArea"][destElement[0]]["rows"].map(
          (rows: any, rowIndex: number) => {
            if (rowIndex === rowId) {
              const cols = rows["columns"].map((col: any, colIndex: number) => {
                if (colIndex === colId) {
                  return {
                    ...col,
                    itemIds,
                  };
                }
                return col;
              });
              return {
                ...rows,
                columns: cols,
              };
            }
            return rows;
          }
        );

        setDndData({
          ...data,
          editorArea: {
            ...data["editorArea"],
            [destElement[0]]: {
              ...data["editorArea"][destElement[0]],
              rows: updated,
            },
          },
          formElements: {
            ...data["formElements"],
            [originalLocation.column]: {
              ...data["formElements"][originalLocation.column],
              itemIds: dest,
            },
          },
        });
      } else {
        setDndData({
          ...data,
          editorArea: {
            ...data["editorArea"],
            [column]: {
              ...data["editorArea"][column],
              itemIds,
            },
          },
          formElements: {
            ...data["formElements"],
            [originalLocation.column]: {
              ...data["formElements"][originalLocation.column],
              itemIds: dest,
            },
          },
        });
      }
    }

    // Remove styles
    const colIdToDelete = getColId(column);

    if (
      styling !== undefined &&
      styling.hasOwnProperty("styles") > 0 &&
      styling.styles[colIdToDelete] !== undefined
    ) {
      const allStyles = styling.styles;
      delete allStyles[colIdToDelete];

      setStyles({
        ...styling,
        styles: allStyles,
      });
    }
  };

  const printComponentLabel = (itemId: string) => {
    const items: any = defaults.items;

    return (
      <div
        style={{
          marginTop: "6px",
          marginLeft: "10px",
          display: "inline-block",
        }}
      >
        {dragAndDropLabelStrings?.[itemId] ?? items[itemId].content}
      </div>
    );
  };

  if (typeof item === "undefined") return <></>;
  else
    return (
      <div style={{ width: "100%", display: "flex", fontSize: "14px" }}>
        {sectionId !== "formTable" ? printComponentLabel(item.id) : ""}
        <div
          className={`${sectionId} manage-column${
            snapshot.isDragging ? " dragging" : ""
          }`}
        >
          <Tooltip
            title={`${settings_for} ${
              sectionId === "formTable" ? column_str : item_str
            }.`}
          >
            <FormOutlined
              style={{
                display: item.id === "add-to-cart-checkbox" ? "none" : "",
              }}
              onClick={() =>
                setShowStyling({
                  show: true,
                  type: "ITEM",
                  id: sectionId === "formTable" ? item.id : getColId(sectionId),
                  itemId: item.id,
                  section: sectionId,
                })
              }
            />
          </Tooltip>
          <Tooltip
            title={`${
              sectionId === "formTable" ? reorder_column : reorder_item
            }`}
          >
            <DragOutlined {...provided.dragHandleProps} />
          </Tooltip>
          <Popconfirm
            title={`${delete_msg} ${
              sectionId === "formTable" ? column_str : item_str
            }?`}
            onConfirm={(e: any) => {
              setShowStyling({ show: false });
              deletingItem(item.id, sectionId);
            }}
            okText={yes_str}
            cancelText={no_str}
          >
            <DeleteOutlined />
          </Popconfirm>
        </div>
      </div>
    );
};

const mapStateToProps = (store: any, props: any) => ({
  manageColumnStrings: store.i18n.backend.manage_column,
  dragAndDropLabelStrings: store.i18n.backend.drag_and_drop_labels,
});

export default connect(mapStateToProps)(ManageColumn);
