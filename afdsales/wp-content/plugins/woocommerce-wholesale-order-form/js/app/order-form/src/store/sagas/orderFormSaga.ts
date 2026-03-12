import { takeEvery, put, call } from "redux-saga/effects";
import { EOrderFormActionTypes, IResponseGenerator } from "types/index";
import { orderFormActions, orderFormDataActions } from "store/actions/index";
import axiosInstance from "helpers/axios";

declare var WWOF_Frontend_Options: any;

let headers = {};

if (
  typeof WWOF_Frontend_Options !== "undefined" &&
  WWOF_Frontend_Options.nonce !== ""
) {
  headers = {
    "X-WP-Nonce": WWOF_Frontend_Options.nonce,
  };
}

export function* fetchProducts(action: any) {
  const {
    orderFormData,
    search,
    category,
    active_page,
    searching,
    sort_order,
    show_all,
    products,
    categories,
    attributes,
    wholesale_role,
    per_page,
    sort_by,
    allow_sku_search,
    form_settings,
  } = action.payload;

  try {
    if (attributes.id !== undefined) {
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [attributes.id]: {
            ...orderFormData.formProducts[attributes.id],
            fetching: true,
          },
        })
      );
    }

    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_products",
          search,
          category,
          page: active_page || 1,
          searching: searching || "no",
          sort_order: sort_order || [],
          sort_by: sort_by || "",
          products: products || [],
          categories,
          show_all,
          wholesale_role,
          per_page,
          allow_sku_search: allow_sku_search || "",
          form_settings,
        })
      )
    );

    if (response && response.data) {
      const data = { ...response.data, attributes, active_page, sort_order };
      const total_products = parseInt(response.data.total_products);

      if (attributes.id !== undefined && attributes.id !== 0) {
        yield put(orderFormActions.setAppState({ data }));

        yield put(
          orderFormDataActions.setCartSubtotal({
            [attributes.id]: {
              cartSubtotal: data.cart_subtotal,
            },
          })
        );

        yield put(
          orderFormDataActions.setOrderFormProducts({
            [attributes.id]: {
              fetching: false,
              products: data.products,
              variations: data.variations || [],
              lazy_load_variations_data: data.lazy_load_variations_data,
            },
          })
        );

        if (typeof data.settings !== "undefined") {
          yield put(
            orderFormDataActions.setOrderFormPagination({
              orderFormId: attributes.id,
              data: {
                active_page,
                per_page,
                total_products,
                total_page: parseInt(data.total_page),
              },
            })
          );
        }
      }
    }
  } catch (e) {
    console.log(e);
  }
}

export function* fetchCategories(action: any) {
  const { categories } = action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_categories",
          categories: categories,
        })
      )
    );

    if (response && response.data) {
      const data = { ...response.data };

      yield put(
        orderFormActions.setCategories({ categories: data.categories })
      );
    }
  } catch (e) {
    console.log(e);
  }
}

// Load more variations - lazy loading
export function* loadMoreVariations(action: any) {
  const {
    orderFormId,
    wholesale_role,
    product_id,
    current_page,
    successCB,
    failCB,
    orderFormData,
  } = action.payload;

  try {
    let ajax_action =
      wholesale_role !== ""
        ? "wwof_api_get_wholesale_variations"
        : "wwof_api_get_variations";

    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: ajax_action,
          product_id,
          current_page,
          wholesale_role,
          uid: WWOF_Frontend_Options.uid,
          form_settings: orderFormData.formSettings[orderFormId],
        })
      )
    );

    if (response && response.data.status === "success") {
      // Append more variations
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [orderFormId]: {
            ...orderFormData.formProducts[orderFormId],
            variations: {
              ...orderFormData.formProducts[orderFormId].variations,
              [product_id]: [
                ...orderFormData.formProducts[orderFormId].variations[
                  product_id
                ],
                ...response.data.variations,
              ],
            },
            lazy_load_variations_data: {
              ...orderFormData.formProducts[orderFormId]
                .lazy_load_variations_data,
              [product_id]: {
                ...orderFormData.formProducts[orderFormId]
                  .lazy_load_variations_data[product_id],
                current_page,
              },
            },
          },
        })
      );

      if (typeof successCB === "function") {
        successCB(response);
      }
    } else if (typeof failCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

// Load more products - lazy loading
export function* loadMoreProducts(action: any) {
  const {
    orderFormId,
    orderFormData,
    active_page,
    per_page,
    wholesale_role,
    successCB,
    failCB,
  } = action.payload;

  try {
    const qs = require("qs");
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(
        WWOF_Frontend_Options.ajax,
        qs.stringify({
          action: "wwof_api_get_products",
          per_page,
          page: active_page,
          wholesale_role,
          form_settings: orderFormData.formSettings[orderFormId],
        })
      )
    );

    if (response && response.data && response.data.status === "success") {
      // Add more products to the state
      yield put(
        orderFormDataActions.setOrderFormProducts({
          [orderFormId]: {
            ...orderFormData.formProducts[orderFormId],
            products: [
              ...orderFormData.formProducts[orderFormId].products,
              ...response.data.products,
            ],
            variations: {
              ...orderFormData.formProducts[orderFormId].variations,
              ...response.data.variations,
            },
            lazy_load_variations_data: {
              ...orderFormData.formProducts[orderFormId]
                .lazy_load_variations_data,
              ...response.data.lazy_load_variations_data,
            },
          },
        })
      );

      // Update pagination active page
      yield put(
        orderFormDataActions.setOrderFormPagination({
          orderFormId,
          data: {
            active_page,
          },
        })
      );
      if (typeof successCB === "function") {
        successCB(response);
      }
    } else if (typeof failCB === "function") failCB();
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EOrderFormActionTypes.FETCH_PRODUCTS, fetchProducts),
  takeEvery(EOrderFormActionTypes.FETCH_CATEGORIES, fetchCategories),
  takeEvery(EOrderFormActionTypes.LOAD_MORE_VARIATIONS, loadMoreVariations),
  takeEvery(EOrderFormActionTypes.LOAD_MORE_PRODUCTS, loadMoreProducts),
];
