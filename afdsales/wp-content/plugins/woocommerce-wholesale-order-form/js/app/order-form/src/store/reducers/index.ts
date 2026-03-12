import { combineReducers } from "redux";

// Reducers
import orderFormReducer from "./orderFormReducer";
import orderFormDataReducer from "./orderFormDataReducer";
import i18nReducer from "./i18nReducer";

const reducers = combineReducers({
  orderForm: orderFormReducer,
  orderFormData: orderFormDataReducer,
  i18n: i18nReducer,
});

export default reducers;
