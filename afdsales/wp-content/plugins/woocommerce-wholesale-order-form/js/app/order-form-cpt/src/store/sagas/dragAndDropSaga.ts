import { takeEvery, put } from "redux-saga/effects";

// Actions
import { dragAndDropActions } from "store/actions/index";
import { EDragAndDropActionTypes } from "types/index";

export function* transferItems(action: any) {
  try {
    const { source, destination, data, destElement } = action.payload;
    let sourceColumn = [];
    let destColumn = [];

    const rowId = parseInt(destElement[1]);
    const colId = parseInt(destElement[2]);

    // Header and Footer only
    if (destElement.length >= 3) {
      sourceColumn = data["formElements"][source.droppableId];
      destColumn =
        data["editorArea"][destElement[0]]["rows"][rowId]["columns"][colId];
    } else {
      // Form Table
      sourceColumn = data["formElements"][source.droppableId];
      destColumn = data["editorArea"][destination.droppableId];
    }

    const sourceItems = [...sourceColumn.itemIds];
    const destItems = [...destColumn.itemIds];

    const [removed] = sourceItems.splice(source.index, 1);
    destItems.splice(destination.index, 0, removed);

    if (destElement.length >= 3) {
      // Header and Footer

      // Handle updating 2x nested array of objects
      let updated = data["editorArea"][destElement[0]]["rows"].map(
        (row: any, rowIndex: number) => {
          if (rowIndex === rowId) {
            const cols = row["columns"].map((col: any, colIndex: number) => {
              if (colIndex === colId) {
                return {
                  ...col,
                  itemIds: destItems,
                };
              }
              return col;
            });
            return {
              ...row,
              columns: cols,
            };
          }
          return row;
        }
      );

      yield put(
        dragAndDropActions.setDndData({
          ...data,
          formElements: {
            ...data["formElements"],
            [source.droppableId]: {
              ...sourceColumn,
              itemIds: sourceItems,
            },
          },
          editorArea: {
            ...data["editorArea"],
            [destElement[0]]: {
              // Add to form header or form footer
              ...data["editorArea"][destElement[0]],
              rows: updated,
            },
          },
        })
      );
    } else {
      // Add to form table

      yield put(
        dragAndDropActions.setDndData({
          ...data,
          formElements: {
            ...data["formElements"],
            [source.droppableId]: {
              ...sourceColumn,
              itemIds: sourceItems,
            },
          },
          editorArea: {
            ...data["editorArea"],
            [destination.droppableId]: {
              ...destColumn,
              itemIds: destItems,
            },
          },
        })
      );
    }
  } catch (e) {
    console.log(e);
  }
}

export function* arrangeTableColumns(action: any) {
  try {
    const { source, destination, data, destElement } = action.payload;
    const itemIds = data.editorArea[destElement[0]]["itemIds"];
    const [moved] = itemIds.splice(source.index, 1);

    itemIds.splice(destination.index, 0, moved);

    yield put(
      dragAndDropActions.setDndData({
        ...data,
        editorArea: {
          ...data["editorArea"],
          [destElement[0]]: {
            ...data.editorArea[destElement[0]],
            itemIds,
          },
        },
      })
    );
  } catch (e) {
    console.log(e);
  }
}

export function* arrangeHeaderFooterRows(action: any) {
  try {
    const { source, destination, data, destElement } = action.payload;
    const rows = data["editorArea"][destElement[0]];
    const rowsList = [...rows["rows"]];

    const [removed] = rowsList.splice(source.index, 1);
    rowsList.splice(destination.index, 0, removed);

    yield put(
      dragAndDropActions.setDndData({
        ...data,
        editorArea: {
          ...data["editorArea"],
          [destElement[0]]: {
            ...data["editorArea"][destElement[0]],
            rows: rowsList,
          },
        },
      })
    );
  } catch (e) {
    console.log(e);
  }
}

export function* arrangeHeaderFooterColumns(action: any) {
  try {
    const { source, destination, data, destElement } = action.payload;
    const rowId = parseInt(destElement[1]);
    const rowCols = data["editorArea"][destElement[0]]["rows"][rowId];
    const rowColsList = [...rowCols["columns"]];

    const [removed] = rowColsList.splice(source.index, 1);
    rowColsList.splice(destination.index, 0, removed);

    // Handle updating nested array of objects
    let updated = data["editorArea"][destElement[0]]["rows"].map(
      (row: any, rowIndex: number) => {
        if (rowIndex === rowId) {
          return {
            ...row,
            columns: rowColsList,
          };
        }
        return row;
      }
    );

    yield put(
      dragAndDropActions.setDndData({
        ...data,
        editorArea: {
          ...data["editorArea"],
          [destElement[0]]: {
            ...data["editorArea"][destElement[0]],
            rows: updated,
          },
        },
      })
    );
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EDragAndDropActionTypes.TRANSFER_ITEMS, transferItems),
  takeEvery(EDragAndDropActionTypes.ARRANGE_TABLE_COLUMNS, arrangeTableColumns),
  takeEvery(
    EDragAndDropActionTypes.ARRANGE_HEADER_FOOTER_ROWS,
    arrangeHeaderFooterRows
  ),
  takeEvery(
    EDragAndDropActionTypes.ARRANGE_HEADER_FOOTER_COLUMNS,
    arrangeHeaderFooterColumns
  ),
];
