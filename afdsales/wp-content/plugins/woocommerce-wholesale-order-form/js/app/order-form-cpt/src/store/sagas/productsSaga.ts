import { takeEvery, put, call } from "redux-saga/effects";
import { EProductActionTypes, IResponseGenerator } from "types/index";

import {
  productActions,
  orderFormActions,
  paginationActions,
} from "store/actions/index";

import axios from "axios";
declare var Options: any;

export function* fetchProducts(action: any) {
  try {
    yield put(productActions.setFetchingProducts(true));

    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axios.post(
        Options.ajax,
        qs.stringify({
          action: "wwof_api_get_products",
          ...action.payload,
        })
      )
    );

    if (response && response.data) {
      yield put(productActions.setFetchingProducts(false));
      yield put(orderFormActions.setCartSubtotal(response.data.cart_subtotal));
      yield put(productActions.setProducts(response.data.products));
      yield put(productActions.setVariations(response.data.variations));
      yield put(
        paginationActions.setPaginationState({
          active_page: 1,
          per_page: 10,
          total_products: parseInt(response.data.total_products),
        })
      );

      // Lazy loading data
      yield put(
        productActions.setLazyLoadData(response.data.lazy_load_variations_data)
      );
    } else console.log(response);
  } catch (e) {
    console.log(e);
  }
}

export function* fetchCategories(action: any) {
  const { categories } = action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axios.post(
        Options.ajax,
        qs.stringify({
          action: "wwof_api_get_categories",
          categories: categories,
        })
      )
    );

    if (response && response.data) {
      yield put(productActions.setCategories(response.data.categories));
    }
  } catch (e) {
    console.log(e);
  }
}

// Load more variations - lazy loading
export function* loadMoreVariations(action: any) {
  const { orderForm, products, product_id, current_page, successCB, failCB } =
    action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axios.post(
        Options.ajax,
        qs.stringify({
          action: "wwof_api_get_variations",
          product_id,
          current_page,
          form_settings: {
            ...orderForm.settingsData,
          },
        })
      )
    );

    if (response && response.data.status === "success") {
      // Add more variations
      yield put(
        productActions.setVariations({
          ...products.variations,
          [product_id]: [
            ...products.variations[product_id],
            ...response.data.variations,
          ],
        })
      );

      // Lazy loading data
      yield put(
        productActions.setLazyLoadData({
          ...products.lazy_load_variations_data,
          [product_id]: {
            ...products.lazy_load_variations_data[product_id],
            current_page,
          },
        })
      );

      if (typeof successCB === "function") {
        successCB(response);
      }
    } else if (typeof successCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EProductActionTypes.FETCH_PRODUCTS, fetchProducts),
  takeEvery(EProductActionTypes.FETCH_CATEGORIES, fetchCategories),
  takeEvery(EProductActionTypes.LOAD_MORE_VARIATIONS, loadMoreVariations),
];
