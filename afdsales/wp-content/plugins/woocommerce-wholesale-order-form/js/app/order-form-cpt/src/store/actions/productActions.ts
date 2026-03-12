import { EProductActionTypes } from "types/index";

export const productActions = {
  setFetchingProducts: (payload: any) => ({
    type: EProductActionTypes.SET_FETCHING_PRODUCTS,
    payload,
  }),
  fetchProducts: (payload: any) => ({
    type: EProductActionTypes.FETCH_PRODUCTS,
    payload,
  }),
  setProducts: (payload: any) => ({
    type: EProductActionTypes.SET_PRODUCTS,
    payload,
  }),
  setVariations: (payload: any) => ({
    type: EProductActionTypes.SET_VARIATIONS,
    payload,
  }),
  fetchCategories: (payload: any) => ({
    type: EProductActionTypes.FETCH_CATEGORIES,
    payload,
  }),
  setCategories: (payload: any) => ({
    type: EProductActionTypes.SET_CATEGORIES,
    payload,
  }),
  setLazyLoadData: (payload: any) => ({
    type: EProductActionTypes.SET_LAZY_LOAD_DATA,
    payload,
  }),
  loadMoreVariations: (payload: any) => ({
    type: EProductActionTypes.LOAD_MORE_VARIATIONS,
    payload,
  }),
};
