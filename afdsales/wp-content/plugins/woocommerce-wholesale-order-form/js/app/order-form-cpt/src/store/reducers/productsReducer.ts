import { EProductActionTypes, IProducts } from "types/index";

const defaults = {
  fetchingProducts: false,
  products: [],
  categories: [],
  variations: [],
  lazy_load_variations_data: {},
};

export default function productsReducer(
  state: IProducts = defaults,
  action: any
) {
  switch (action.type) {
    case EProductActionTypes.SET_FETCHING_PRODUCTS:
      return {
        ...state,
        fetchingProducts: action.payload,
      };

    case EProductActionTypes.SET_PRODUCTS:
      return {
        ...state,
        products: action.payload,
      };

    case EProductActionTypes.SET_CATEGORIES:
      return {
        ...state,
        categories: action.payload,
      };

    case EProductActionTypes.SET_VARIATIONS:
      return {
        ...state,
        variations: action.payload,
      };
    case EProductActionTypes.SET_LAZY_LOAD_DATA:
      return {
        ...state,
        lazy_load_variations_data: action.payload,
      };
    default:
      return state;
  }
}
