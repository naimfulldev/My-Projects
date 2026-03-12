// ORDER FORM
export enum EOrderFormActionTypes {
  SET_LOADING = "SET_LOADING",
  SET_ORDER_FORM_DATA = "SET_ORDER_FORM_DATA",
  SET_CART_SUBTOTAL = "SET_CART_SUBTOTAL",
  FETCH_ORDER_FORMS = "FETCH_ORDER_FORMS",
  FETCH_ORDER_FORM = "FETCH_ORDER_FORM",
  ADD_NEW_ORDER_FORM = "ADD_NEW_ORDER_FORM",
  EDIT_ORDER_FORM = "EDIT_ORDER_FORM",
  DELETE_ORDER_FORM = "DELETE_ORDER_FORM",
  SET_PAGE = "SET_PAGE",
  GET_ORDER_FORM_SETTINGS = "GET_ORDER_FORM_SETTINGS",
  SET_ORDER_FORM_SETTINGS = "SET_ORDER_FORM_SETTINGS",
  GET_ORDER_FORM_SETTINGS_DATA = "GET_ORDER_FORM_SETTING_DATA",
  SET_ORDER_FORM_SETTINGS_DATA = "SET_ORDER_FORM_SETTING_DATA",
  SET_SELECTED_CATEGORY = "SET_SELECTED_CATEGORY",
  SET_FILTERED_CATEGORIES = "SET_FILTERED_CATEGORIES",
  SET_TAX_DISPLAY = "SET_TAX_DISPLAY",
  RESET_ORDER_FORM_DATA = "RESET_ORDER_FORM_DATA",
  SET_EXCLUDED_CATEGORIES = "SET_EXCLUDED_CATEGORIES",
  SET_SUBTOTAL_SUFFIX = "SET_SUBTOTAL_SUFFIX",
  SET_SUBTOTAL_PRE_TEXT = "SET_SUBTOTAL_PRE_TEXT",
  SET_QUANTITY_RESTRICTION = "SET_QUANTITY_RESTRICTION",
  SET_TABLE_INCLUDED_CATEGORY = "SET_TABLE_INCLUDED_CATEGORY",
}

export interface IOrderForm {
  data: any[];
  pagination: {
    page: number;
    defaultCurrent: number;
    totalPages: number;
    total: number;
    pageSize: number;
  };
  settings: object;
  settingsData: object;
  selectedCategory: string;
  filteredCategories: string[];
  excludedCategories: string[];
  cartSubtotal: string;
  taxDisplay: string;
  subtotalPreText: string;
  subtotalSuffix: string;
  quantityRestriction: boolean;
  tableIncludedCategory: string[];
}
