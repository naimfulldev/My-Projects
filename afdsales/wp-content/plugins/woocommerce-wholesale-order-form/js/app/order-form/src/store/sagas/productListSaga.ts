import { takeEvery, call } from "redux-saga/effects";
import { EProductListActionTypes } from "types/ProductListTypes";

import axios from "helpers/axios";
import { IResponseGenerator } from "types/IResponseGenerator";

declare var WWOF_Frontend_Options: any;

export function* addProductToCart(action: any) {
  const {
    product_type,
    product_id,
    variation_id,
    quantity,
    successCB,
    failCB,
    form_settings,
  } = action.payload;

  try {
    const qs = require("qs");

    const response: IResponseGenerator = yield call(() =>
      axios.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_add_product_to_cart",
          product_type: product_type,
          product_id: product_id,
          variation_id: variation_id,
          quantity: quantity,
          form_settings: form_settings,
        })
      )
    );

    if (response && response.data.status === "success") {
      if (typeof successCB === "function") successCB(response);
    } else if (response.data.status === "failed") {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export function* addProductsToCart(action: any) {
  const { products, successCB, failCB, form_settings } = action.payload;

  try {
    const qs = require("qs");

    const response: IResponseGenerator = yield call(() =>
      axios.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_add_products_to_cart",
          products: products,
          form_settings: form_settings,
        })
      )
    );

    if (response && response.data.status === "success") {
      if (typeof successCB === "function") successCB(response);
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EProductListActionTypes.ADD_PRODUCT_TO_CART, addProductToCart),
  takeEvery(EProductListActionTypes.ADD_PRODUCTS_TO_CART, addProductsToCart),
];
