import { EOrderFormActionTypes } from "types/index";

export const orderFormActions = {
  setOrderFormData: (payload: any) => ({
    type: EOrderFormActionTypes.SET_ORDER_FORM_DATA,
    payload,
  }),
  setCartSubtotal: (payload: any) => ({
    type: EOrderFormActionTypes.SET_CART_SUBTOTAL,
    payload,
  }),
  fetchOrderForms: (payload: any) => ({
    type: EOrderFormActionTypes.FETCH_ORDER_FORMS,
    payload,
  }),
  fetchOrderForm: (payload: any) => ({
    type: EOrderFormActionTypes.FETCH_ORDER_FORM,
    payload,
  }),
  addNewOrderForm: (payload: any) => ({
    type: EOrderFormActionTypes.ADD_NEW_ORDER_FORM,
    payload,
  }),
  editOrderForm: (payload: any) => ({
    type: EOrderFormActionTypes.EDIT_ORDER_FORM,
    payload,
  }),
  deleteOrderForm: (payload: any) => ({
    type: EOrderFormActionTypes.DELETE_ORDER_FORM,
    payload,
  }),
  setPage: (payload: any) => ({
    type: EOrderFormActionTypes.SET_PAGE,
    payload,
  }),
  getOrderFormSettings: (payload: any) => ({
    type: EOrderFormActionTypes.GET_ORDER_FORM_SETTINGS,
    payload,
  }),
  setOrderFormSettings: (payload: any) => ({
    type: EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS,
    payload,
  }),
  setOrderFormSettingsData: (payload: any) => ({
    type: EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS_DATA,
    payload,
  }),
  setSelectedCategory: (payload: any) => ({
    type: EOrderFormActionTypes.SET_SELECTED_CATEGORY,
    payload,
  }),
  setFilteredCategories: (payload: any) => ({
    type: EOrderFormActionTypes.SET_FILTERED_CATEGORIES,
    payload,
  }),
  setTaxDisplay: (payload: any) => ({
    type: EOrderFormActionTypes.SET_TAX_DISPLAY,
    payload,
  }),
  resetOrderFormData: (payload: any) => ({
    type: EOrderFormActionTypes.RESET_ORDER_FORM_DATA,
    payload,
  }),
  setExcludedCategories: (payload: any) => ({
    type: EOrderFormActionTypes.SET_EXCLUDED_CATEGORIES,
    payload,
  }),
  setSubtotalPreText: (payload: any) => ({
    type: EOrderFormActionTypes.SET_SUBTOTAL_PRE_TEXT,
    payload,
  }),
  setSubtotalSuffix: (payload: any) => ({
    type: EOrderFormActionTypes.SET_SUBTOTAL_SUFFIX,
    payload,
  }),
  setQuantityRestriction: (payload: any) => ({
    type: EOrderFormActionTypes.SET_QUANTITY_RESTRICTION,
    payload,
  }),
  setTableIncludedCategory: (payload: any) => ({
    type: EOrderFormActionTypes.SET_TABLE_INCLUDED_CATEGORY,
    payload,
  }),
};
