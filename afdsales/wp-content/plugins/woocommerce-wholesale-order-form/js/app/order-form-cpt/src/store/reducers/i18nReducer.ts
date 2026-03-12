import { EOrderFormI18nActionTypes, II18n } from "types/index";

export const defaults = {
  backend: [],
};

export default function i18nReducer(state: II18n = defaults, action: any) {
  switch (action.type) {
    case EOrderFormI18nActionTypes.SET_STRINGS:
      return {
        backend: action.payload,
      };

    default:
      return state;
  }
}
