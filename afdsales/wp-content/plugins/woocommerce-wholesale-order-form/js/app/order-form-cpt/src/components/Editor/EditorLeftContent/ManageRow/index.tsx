import { FormOutlined, DeleteOutlined, DragOutlined } from "@ant-design/icons";
import { defaults } from "store/reducers/dragAndDropReducer";
import { Tooltip, Popconfirm } from "antd";

// Redux
import { connect } from "react-redux";

// Handles Deleting Rows
const ManageRow = (props: any) => {
  const {
    sectionId,
    row,
    data,
    setData,
    setShowStyling,
    styling,
    setStyles,
    provided,
    snapshot,
    manageRowStrings,
  } = props;

  const { settings_for, reorder_row, delete_msg, yes_str, no_str } =
    manageRowStrings;

  const rowId = data["editorArea"][sectionId]["rows"][row]["rowId"];
  const dndData: any = defaults;

  const findOriginalLocation = (id: number) => {
    for (let column of Object.keys(dndData["formElements"])) {
      const index = dndData["formElements"][column]["itemIds"].indexOf(id);
      if (index >= 0) return { index, column };
    }
    return null;
  };

  const deletingRow = () => {
    const rows = data["editorArea"][sectionId]["rows"];

    // Return items into right side draggable items
    // let moved: any = {};
    const colsData = rows[row]["columns"];

    Object.keys(colsData).forEach((d: any, i: number) => {
      const items = colsData[i]["itemIds"];
      items.forEach((item: any, ii: number) => {
        const originalLocation: any = findOriginalLocation(item);
        const dest = data["formElements"][originalLocation.column]["itemIds"];
        dest.splice(originalLocation.index, 0, item);
      });
    });

    rows.splice(row, 1);

    setData({
      ...data,
      editorArea: {
        ...data["editorArea"],
        [sectionId]: {
          ...data["editorArea"][sectionId],
          rows,
        },
      },
    });

    // Remove styles
    if (styling.styles[rowId] !== undefined) {
      const allStyles = styling.styles;
      delete allStyles[rowId];

      setStyles({
        ...styling,
        styles: allStyles,
      });
    }
  };

  return (
    <div
      className={`manage-row${snapshot.isDragging ? " dragging" : ""}`}
      style={{ fontSize: "14px" }}
    >
      <Tooltip title={settings_for}>
        <FormOutlined
          onClick={() =>
            setShowStyling({
              show: true,
              type: "ROW",
              id: rowId,
              itemId: "none",
              section: sectionId,
            })
          }
        />
      </Tooltip>
      <Tooltip title={reorder_row}>
        <DragOutlined {...provided.dragHandleProps} />
      </Tooltip>
      <Popconfirm
        title={delete_msg}
        onConfirm={(e: any) => {
          setShowStyling({ show: false });
          deletingRow();
        }}
        okText={yes_str}
        cancelText={no_str}
      >
        <DeleteOutlined />
      </Popconfirm>
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  manageRowStrings: store.i18n.backend.manage_row,
});

export default connect(mapStateToProps)(ManageRow);
