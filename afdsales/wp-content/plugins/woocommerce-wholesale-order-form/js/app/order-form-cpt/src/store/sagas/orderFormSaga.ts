import { takeEvery, put, call } from "redux-saga/effects";
import { orderFormActions } from "store/actions/index";
import { EOrderFormActionTypes, IResponseGenerator } from "types/index";
import axiosInstance from "helpers/axios";

export function* fetchOrderForms(action: any) {
  try {
    const { page } = action.payload;

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/order_forms/`, {
        params: { page },
      })
    );

    if (response && response.data) {
      yield put(
        orderFormActions.setOrderFormData({
          data: response.data,
          pagination: {
            total: parseInt(response.headers["x-wp-total"]),
            totalPages: parseInt(response.headers["x-wp-totalpages"]),
          },
        })
      );
    }
  } catch (e) {
    console.log(e);
  }
}

export function* fetchOrderForm(action: any) {
  try {
    const { id, successCB, failCB } = action.payload;

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/order_forms/${id}`)
    );

    if (response && response.data) {
      if (typeof successCB === "function") successCB(response.data);
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    const { failCB } = action.payload;
    console.log(e);
    if (typeof failCB === "function") failCB();
  }
}

export function* addOrderForm(action: any) {
  try {
    const { data, successCB, failCB } = action.payload;

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(`wwof/v1/order_forms/`, {
        ...data,
      })
    );

    if (response && response.data) {
      if (typeof successCB === "function") successCB(response.data);
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export function* editOrderForm(action: any) {
  try {
    const { data, successCB, failCB } = action.payload;

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.post(`wwof/v1/order_forms/${data.id}`, {
        ...data,
      })
    );

    if (response && response.data) {
      if (typeof successCB === "function") successCB();
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export function* deleteOrderForm(action: any) {
  try {
    const { pagination, post_id, successCB, failCB } = action.payload;
    const params = { post_ids: post_id, force: true };

    const response: IResponseGenerator = yield call(() =>
      axiosInstance.delete(
        `wwof/v1/order_forms/${Array.isArray(post_id) ? 0 : post_id}`,
        {
          data: params,
        }
      )
    );

    if (response && response.data) {
      let p = pagination.page;

      if (pagination.total % pagination.pageSize === 1 && p - 1 !== 0) p -= 1;
      else if (p - 1 === 0) p = 1;

      yield put(orderFormActions.setPage({ page: p }));
      yield put(orderFormActions.fetchOrderForms({ page: p }));

      if (typeof successCB === "function") successCB();
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export function* getOrderFormSettings(action: any) {
  try {
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/settings/`)
    );

    if (response && response.data)
      yield put(orderFormActions.setOrderFormSettings(response.data));
    else console.log(response);
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EOrderFormActionTypes.FETCH_ORDER_FORMS, fetchOrderForms),
  takeEvery(EOrderFormActionTypes.FETCH_ORDER_FORM, fetchOrderForm),
  takeEvery(EOrderFormActionTypes.ADD_NEW_ORDER_FORM, addOrderForm),
  takeEvery(EOrderFormActionTypes.EDIT_ORDER_FORM, editOrderForm),
  takeEvery(EOrderFormActionTypes.DELETE_ORDER_FORM, deleteOrderForm),
  takeEvery(
    EOrderFormActionTypes.GET_ORDER_FORM_SETTINGS,
    getOrderFormSettings
  ),
];
