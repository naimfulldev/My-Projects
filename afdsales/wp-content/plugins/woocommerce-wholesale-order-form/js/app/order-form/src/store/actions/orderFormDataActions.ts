import { EOrderFormDataActionTypes } from "types/OrderFormDataTypes";

export const orderFormDataActions = {
  fetchOrderFormData: (payload: any) => ({
    type: EOrderFormDataActionTypes.FETCH_ORDER_FORM_DATA,
    payload,
  }),
  setOrderFormData: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_ORDER_FORM_DATA,
    payload,
  }),
  setOrderFormProducts: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_ORDER_FORM_PRODUCTS,
    payload,
  }),
  setOrderFormPagination: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_ORDER_FORM_PAGINATION,
    payload,
  }),
  setPageSelectedAll: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_PAGE_SELECTED_ALL,
    payload,
  }),
  setFormFilters: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_ORDER_FORM_FILTERS,
    payload,
  }),
  setSelectedProductsToAddToCart: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_ORDER_FORM_SELECTED_PRODUCTS_TO_ADD_CART,
    payload,
  }),
  updateSelectedProducts: (payload: any) => ({
    type: EOrderFormDataActionTypes.UPDATE_ORDER_FORM_SELECTED_PRODUCTS,
    payload,
  }),
  setCartSubtotal: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_ORDER_FORM_CART_SUBTOTAL,
    payload,
  }),
  setTableSorter: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_ORDER_FORM_TABLE_SORTER,
    payload,
  }),
  getMinOrderReqNotice: (payload: any) => ({
    type: EOrderFormDataActionTypes.GET_MIN_ORDER_REQ_NOTICE,
    payload,
  }),
  setMinOrderReqNotice: (payload: any) => ({
    type: EOrderFormDataActionTypes.SET_MIN_ORDER_REQ_NOTICE,
    payload,
  }),
};
