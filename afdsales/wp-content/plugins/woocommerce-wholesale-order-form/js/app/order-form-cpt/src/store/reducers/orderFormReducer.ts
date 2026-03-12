import { EOrderFormActionTypes, IOrderForm } from "types/index";

export const defaults = {
  // List of order forms,
  data: [],
  // Pagination data
  pagination: {
    page: 1,
    defaultCurrent: 1,
    totalPages: 0,
    total: 0,
    pageSize: 10,
  },
  // List of settings under "Settings" tab
  settings: {},
  // List of components settings that needs to be saved in postmeta to be used in the frontend
  settingsData: {},
  // The selected category set in the category filter component
  selectedCategory: "",
  // List of categories to be used as options in the dropdown filter
  filteredCategories: [],
  // The excluded categories that is not allowed to show in the category filter dropdown set in category filter component
  excludedCategories: [],
  // The cart subtotal
  cartSubtotal: "",
  // The tax display set in the cart subtotal component
  taxDisplay: "",
  // The cart subtotal pretext
  subtotalPreText: "",
  // The subtotal suffix
  subtotalSuffix: "",
  // The quantity restriction option is enabled set in table variation dropdown
  quantityRestriction: false,
  // The included category set in the form table option
  tableIncludedCategory: [],
};

export default function orderFormReducer(
  state: IOrderForm = defaults,
  action: any
) {
  switch (action.type) {
    case EOrderFormActionTypes.SET_ORDER_FORM_DATA:
      const { data, pagination } = action.payload;

      return {
        ...state,
        data,
        pagination: {
          ...state.pagination,
          ...pagination,
        },
      };

    case EOrderFormActionTypes.SET_CART_SUBTOTAL:
      return {
        ...state,
        cartSubtotal: action.payload,
      };

    case EOrderFormActionTypes.SET_PAGE:
      const { page } = action.payload;

      return {
        ...state,
        pagination: {
          ...state.pagination,
          page,
        },
      };

    case EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS:
      return {
        ...state,
        settings: {
          ...state.settings,
          ...action.payload,
        },
      };

    case EOrderFormActionTypes.SET_ORDER_FORM_SETTINGS_DATA:
      return {
        ...state,
        settingsData: {
          ...state.settingsData,
          ...action.payload,
        },
      };

    case EOrderFormActionTypes.SET_SELECTED_CATEGORY:
      return {
        ...state,
        selectedCategory: action.payload,
      };

    case EOrderFormActionTypes.SET_FILTERED_CATEGORIES:
      return {
        ...state,
        filteredCategories: action.payload,
      };

    case EOrderFormActionTypes.SET_TAX_DISPLAY:
      return {
        ...state,
        taxDisplay: action.payload,
      };

    case EOrderFormActionTypes.RESET_ORDER_FORM_DATA:
      return defaults;

    case EOrderFormActionTypes.SET_EXCLUDED_CATEGORIES:
      return {
        ...state,
        excludedCategories: action.payload,
      };
    case EOrderFormActionTypes.SET_SUBTOTAL_PRE_TEXT:
      return {
        ...state,
        subtotalPreText: action.payload,
      };
    case EOrderFormActionTypes.SET_SUBTOTAL_SUFFIX:
      return {
        ...state,
        subtotalSuffix: action.payload,
      };
    case EOrderFormActionTypes.SET_QUANTITY_RESTRICTION:
      return {
        ...state,
        quantityRestriction: action.payload,
      };

    case EOrderFormActionTypes.SET_TABLE_INCLUDED_CATEGORY:
      return {
        ...state,
        tableIncludedCategory: action.payload,
      };

    default:
      return state;
  }
}
