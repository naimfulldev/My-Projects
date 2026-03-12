import { EOrderFormI18nActionTypes } from "types/index";

export const i18nActions = {
  fetchStrings: (payload: any) => ({
    type: EOrderFormI18nActionTypes.FETCH_STRINGS,
    payload,
  }),
  setStrings: (payload: any) => ({
    type: EOrderFormI18nActionTypes.SET_STRINGS,
    payload,
  }),
};
