import { EDragAndDropActionTypes } from "types/index";

export const dragAndDropActions = {
  setDndData: (payload: any) => ({
    type: EDragAndDropActionTypes.SET_DND_DATA,
    payload,
  }),
  resetDndData: (payload: any) => ({
    type: EDragAndDropActionTypes.RESET_DND_DATA,
    payload,
  }),
  transferItems: (payload: any) => ({
    type: EDragAndDropActionTypes.TRANSFER_ITEMS,
    payload,
  }),
  arrangeTableColumns: (payload: any) => ({
    type: EDragAndDropActionTypes.ARRANGE_TABLE_COLUMNS,
    payload,
  }),
  arrangeHeaderFooterRows: (payload: any) => ({
    type: EDragAndDropActionTypes.ARRANGE_HEADER_FOOTER_ROWS,
    payload,
  }),
  arrangeHeaderFooterColumns: (payload: any) => ({
    type: EDragAndDropActionTypes.ARRANGE_HEADER_FOOTER_COLUMNS,
    payload,
  }),
};
