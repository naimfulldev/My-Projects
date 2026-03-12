import { EPaginationActionTypes, IPagination } from "types/index";

const defaults = {
  active_page: 1,
  per_page: 10,
  total_products: 0,
  total_page: 0,
};

export default function paginationReducer(
  state: IPagination = defaults,
  action: any
) {
  switch (action.type) {
    case EPaginationActionTypes.SET_PAGINATION_STATE:
      return {
        ...state,
        ...action.payload,
      };

    default:
      return state;
  }
}
