import { EStylingActionTypes, IStyling } from "types/index";

const defaults = {
  show: false,
  item: {},
  styles: {},
};

export default function stylingReducer(
  state: IStyling = defaults,
  action: any
) {
  switch (action.type) {
    case EStylingActionTypes.SET_SHOW_STYLING:
      const { show, type, id, itemId, section } = action.payload;

      return {
        ...state,
        show,
        item: {
          type: type ? type : state.item.type,
          id: id ? id : state.item.id,
          itemId: itemId ? itemId : state.item.itemId,
          section: section ? section : state.item.section,
        },
      };

    case EStylingActionTypes.SET_STYLES:
      return {
        ...state,
        styles: action.payload.styles,
      };

    default:
      return state;
  }
}
