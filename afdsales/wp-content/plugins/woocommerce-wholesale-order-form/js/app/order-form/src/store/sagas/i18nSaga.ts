import { takeEvery, put, call } from "redux-saga/effects";
import { EOrderFormI18nActionTypes, IResponseGenerator } from "types/index";
import { i18nActions } from "store/actions/index";
import axiosInstance from "helpers/axios";

export function* fetchStrings(action: any) {
  try {
    const response: IResponseGenerator = yield call(() =>
      axiosInstance.get(`wwof/v1/i18n/?source=frontend`)
    );

    if (response && response.data) {
      yield put(i18nActions.setStrings(response.data));
    } else console.log(response);
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(EOrderFormI18nActionTypes.FETCH_STRINGS, fetchStrings),
];
