import { EPaginationActionTypes } from "types/index";

export const paginationActions = {
  setPaginationState: (payload: any) => ({
    type: EPaginationActionTypes.SET_PAGINATION_STATE,
    payload,
  }),
};
