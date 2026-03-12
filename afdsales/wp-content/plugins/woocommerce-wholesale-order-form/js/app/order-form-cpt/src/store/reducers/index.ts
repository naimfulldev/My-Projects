import { combineReducers } from "redux";

// Reducers
import orderFormReducer from "./orderFormReducer";
import dragAndDropReducer from "./dragAndDropReducer";
import stylingReducer from "./stylingReducer";
import productsReducer from "./productsReducer";
import paginationReducer from "./paginationReducer";
import i18nReducer from "./i18nReducer";

const reducers = combineReducers({
  orderForm: orderFormReducer,
  dragAndDrop: dragAndDropReducer,
  styling: stylingReducer,
  products: productsReducer,
  pagination: paginationReducer,
  i18n: i18nReducer,
});

export default reducers;
