import { EStylingActionTypes } from "types/index";

export const stylingActions = {
  setShowStyling: (payload: any) => ({
    type: EStylingActionTypes.SET_SHOW_STYLING,
    payload,
  }),
  setStyles: (payload: any) => ({
    type: EStylingActionTypes.SET_STYLES,
    payload,
  }),
};
