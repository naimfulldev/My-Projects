import { EOrderFormActionTypes } from "types/OrderFormTypes";

export const orderFormActions = {
  fetchProducts: (payload: any) => ({
    type: EOrderFormActionTypes.FETCH_PRODUCTS,
    payload,
  }),
  fetchCategories: (payload: any) => ({
    type: EOrderFormActionTypes.FETCH_CATEGORIES,
    payload,
  }),
  setCategories: (payload: any) => ({
    type: EOrderFormActionTypes.SET_CATEGORIES,
    payload,
  }),
  setAppState: (payload: any) => ({
    type: EOrderFormActionTypes.SET_APP_STATE,
    payload,
  }),
  loadMoreVariations: (payload: any) => ({
    type: EOrderFormActionTypes.LOAD_MORE_VARIATIONS,
    payload,
  }),
  loadMoreProducts: (payload: any) => ({
    type: EOrderFormActionTypes.LOAD_MORE_PRODUCTS,
    payload,
  }),
  setShowModal: (payload: any) => ({
    type: EOrderFormActionTypes.SET_SHOW_MODAL,
    payload,
  }),
};
