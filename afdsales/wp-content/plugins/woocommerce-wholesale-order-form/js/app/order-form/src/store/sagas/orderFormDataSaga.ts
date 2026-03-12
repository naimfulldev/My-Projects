import { takeEvery, put, call } from "redux-saga/effects";
import { EOrderFormDataActionTypes } from "types/OrderFormDataTypes";
import { orderFormDataActions } from "store/actions";
import { IResponseGenerator } from "types/IResponseGenerator";

// Helper
import { getProductMinQtyAndStep } from "helpers/getProductMinQtyAndStep";

import axios from "axios";
import { removePaginationComponent } from "helpers/removePaginationComponent";
import { getComponentSettings } from "helpers/getComponentSettings";

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

export function* fetchOrderFormData(action: any) {
  try {
    const { id, successCB, failCB } = action.payload;

    const response: IResponseGenerator = yield call(() =>
      axios.get(
        `${WWOF_Frontend_Options.site_url}/wp-json/wwof/v1/order_forms/${id}`,
        {
          headers: {
            ...headers,
          },
        }
      )
    );

    if (response && response.data) {
      let formHeader = removePaginationComponent({
        formSettings: response.data.meta.settings,
        formHeader: response.data.meta.editor_area.formHeader,
      });
      let formFooter = removePaginationComponent({
        formSettings: response.data.meta.settings,
        formFooter: response.data.meta.editor_area.formFooter,
      });

      yield put(
        orderFormDataActions.setOrderFormData({
          formTitle: {
            [id]: response.data.title,
          },
          formFooter: {
            [id]: formFooter,
          },
          formHeader: {
            [id]: formHeader,
          },
          formTable: {
            [id]: response.data.meta.editor_area.formTable,
          },
          formStyles: {
            [id]: response.data.meta.styles,
          },
          formSettings: {
            [id]: {
              ...getComponentSettings({
                formStyles: response.data.meta.styles,
              }),
              ...response.data.meta.settings,
            },
          },
        })
      );

      if (typeof successCB === "function") successCB(response);
    } else {
      if (typeof failCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export function* updateSelectedProducts(action: any) {
  try {
    const { selected, orderFormData, orderFormId, product } = action.payload;
    let updatedSelectedProducts: any = orderFormData.formSelectedProducts || {};

    if (selected) {
      if (typeof product !== "undefined") {
        // Per product select
        // Get Wholesale Min Quantity
        let variationName: [] = [];
        let variationID: number = 0;
        let wholesaleData: any =
          typeof product.wholesale_data !== "undefined"
            ? product.wholesale_data
            : [];
        let variationData: any = [];

        if (product.type === "variable") {
          variationID =
            updatedSelectedProducts[orderFormId][product.id].variationID;
        }

        // Get selected variation data and name
        if (variationID != 0) {
          variationData = orderFormData.formProducts[orderFormId]["variations"][
            product.id
          ].find((data: any) => {
            return data.id === variationID;
          });

          if (variationData !== undefined) {
            variationName = variationData.attributes.map((attributes: any) => {
              return `<strong>${attributes.name}</strong>:  ${attributes.option}`;
            });
          }

          variationData =
            typeof variationData.wholesale_data !== "undefined"
              ? variationData.wholesale_data
              : [];
        } else if (product.type === "variation") {
          variationName = product.attributes.map((attributes: any) => {
            return `<strong>${attributes.name}</strong>:  ${attributes.option}`;
          });
        }

        const { minOrderQty } = getProductMinQtyAndStep({
          productType: product.type,
          wholesaleData,
          variationData,
          variationID,
          quantity: 1,
        });

        if (
          typeof updatedSelectedProducts[orderFormId] !== "undefined" &&
          typeof updatedSelectedProducts[orderFormId][product.id] !==
            "undefined"
        ) {
          updatedSelectedProducts = {
            ...updatedSelectedProducts,
            [orderFormId]: {
              ...updatedSelectedProducts[orderFormId],
              [product.id]: {
                quantity: minOrderQty,
                stockStatus: product.stock_status,
                ...updatedSelectedProducts[orderFormId][product.id],
                selected,
                productID: product.id,
                productTitle: product.name,
                productType: product.type,
                wholesaleData,
                variationData,
                variationName: variationName.join("<br/>"),
              },
            },
          };
        } else {
          updatedSelectedProducts = {
            ...updatedSelectedProducts,
            [orderFormId]: {
              ...updatedSelectedProducts[orderFormId],
              [product.id]: {
                selected,
                quantity: minOrderQty,
                stockStatus: product.stock_status,
                productID: product.id,
                productTitle: product.name,
                productType: product.type,
                wholesaleData,
                variationData,
                variationName: variationName.join("<br/>"),
              },
            },
          };
        }

        if (typeof orderFormData.formPagination[orderFormId] !== "undefined") {
          yield put(
            orderFormDataActions.setPageSelectedAll({
              orderFormId,
              data: {
                [orderFormData.formPagination[orderFormId].active_page]: false,
              },
            })
          );
        }
      } else if (
        typeof orderFormData.formProducts[orderFormId] !== "undefined" &&
        typeof orderFormData.formProducts[orderFormId]["products"] !==
          "undefined"
      ) {
        // Selected all
        orderFormData.formProducts[orderFormId]["products"].map(
          (product: any, key: number) => {
            let variationName: [] = [];
            let variationID: number = 0;
            let wholesaleData: any =
              typeof product.wholesale_data !== "undefined"
                ? product.wholesale_data
                : [];
            let variationData: any = [];
            if (
              product.type === "variable" &&
              product.stock_status === "instock"
            ) {
              variationID =
                updatedSelectedProducts[orderFormId][product.id].variationID;
            }

            // Get selected variation data and name
            if (variationID != 0) {
              variationData = orderFormData.formProducts[orderFormId][
                "variations"
              ][product.id].find((data: any) => {
                return data.id === variationID;
              });

              if (variationData !== undefined) {
                variationName = variationData.attributes.map(
                  (attributes: any) => {
                    return attributes.name + ": " + attributes.option;
                  }
                );
              }
              variationData =
                typeof variationData.wholesale_data !== "undefined"
                  ? variationData.wholesale_data
                  : [];
            } else if (product.type === "variation") {
              variationName = product.attributes.map((attributes: any) => {
                return `<strong>${attributes.name}</strong>:  ${attributes.option}`;
              });
            }

            // Get Wholesale Min Quantity
            const { minOrderQty } = getProductMinQtyAndStep({
              productType: product.type,
              wholesaleData,
              variationData,
              variationID,
              quantity: 1,
            });

            if (
              typeof updatedSelectedProducts[orderFormId] !== "undefined" &&
              typeof updatedSelectedProducts[orderFormId][product.id] !==
                "undefined"
            ) {
              updatedSelectedProducts = {
                ...updatedSelectedProducts,
                [orderFormId]: {
                  ...updatedSelectedProducts[orderFormId],
                  [product.id]: {
                    stockStatus: product.stock_status,
                    ...updatedSelectedProducts[orderFormId][product.id],
                    selected,
                    productID: product.id,
                    productTitle: product.name,
                    productType: product.type,
                    quantity: minOrderQty,
                    wholesaleData,
                    variationData,
                    variationName: variationName.join("<br/>"),
                  },
                },
              };
            } else {
              updatedSelectedProducts = {
                ...updatedSelectedProducts,
                [orderFormId]: {
                  ...updatedSelectedProducts[orderFormId],
                  [product.id]: {
                    selected,
                    stockStatus: product.stock_status,
                    productID: product.id,
                    productTitle: product.name,
                    productType: product.type,
                    quantity: minOrderQty,
                    wholesaleData,
                    variationData,
                    variationName: variationName.join("<br/>"),
                  },
                },
              };
            }
          }
        );

        if (typeof orderFormData.formPagination[orderFormId] !== "undefined") {
          yield put(
            orderFormDataActions.setPageSelectedAll({
              orderFormId,
              data: {
                [orderFormData.formPagination[orderFormId].active_page]:
                  selected,
              },
            })
          );
        }
      }
    } else {
      let formSelectedProductsCopy = updatedSelectedProducts[orderFormId];

      // Unselect product checkbox
      if (typeof product !== "undefined") {
        if (product.type === "variable")
          formSelectedProductsCopy[product.id].selected = false;
        else delete formSelectedProductsCopy[product.id];

        if (typeof orderFormData.formPagination[orderFormId] !== "undefined") {
          yield put(
            orderFormDataActions.setPageSelectedAll({
              orderFormId,
              data: {
                [orderFormData.formPagination[orderFormId].active_page]: false,
              },
            })
          );
        }

        // Unselect All checkbox
      } else if (
        typeof orderFormData.formProducts[orderFormId] !== "undefined" &&
        typeof orderFormData.formProducts[orderFormId]["products"] !==
          "undefined"
      ) {
        orderFormData.formProducts[orderFormId]["products"].map(
          (product: any, key: number) => {
            if (
              typeof formSelectedProductsCopy !== "undefined" &&
              typeof formSelectedProductsCopy[product.id] !== "undefined" &&
              formSelectedProductsCopy[product.id].productType !== "variable"
            )
              delete formSelectedProductsCopy[product.id];
            else {
              formSelectedProductsCopy[product.id].selected = false;
            }
          }
        );

        if (typeof orderFormData.formPagination[orderFormId] !== "undefined") {
          yield put(
            orderFormDataActions.setPageSelectedAll({
              orderFormId,
              data: {
                [orderFormData.formPagination[orderFormId].active_page]:
                  selected,
              },
            })
          );
        }
      }

      updatedSelectedProducts = {
        [orderFormId]: {
          ...formSelectedProductsCopy,
        },
      };
    }

    yield put(
      orderFormDataActions.setSelectedProductsToAddToCart(
        updatedSelectedProducts
      )
    );
  } catch (e) {
    console.log(e);
  }
}

export function* getMinOrderReqNotice(action: any) {
  const { postId, wholesale_role, uid, successCB, failCB } = action.payload;

  try {
    const response: IResponseGenerator = yield call(() =>
      axios.get(
        WWOF_Frontend_Options.site_url +
          "/wp-json/wwof/v1/order_forms/get_wholesale_order_requirements",
        {
          params: {
            wholesale_role,
            uid,
          },
          headers: {
            ...headers,
          },
        }
      )
    );

    if (response && response.data.status === "success") {
      yield put(
        orderFormDataActions.setMinOrderReqNotice({
          [postId]: {
            message: response.data.message,
          },
        })
      );

      if (typeof successCB === "function") successCB(response);
    } else {
      if (typeof successCB === "function") failCB();
    }
  } catch (e) {
    console.log(e);
  }
}

export const actionListener = [
  takeEvery(
    EOrderFormDataActionTypes.FETCH_ORDER_FORM_DATA,
    fetchOrderFormData
  ),
  takeEvery(
    EOrderFormDataActionTypes.UPDATE_ORDER_FORM_SELECTED_PRODUCTS,
    updateSelectedProducts
  ),
  takeEvery(
    EOrderFormDataActionTypes.GET_MIN_ORDER_REQ_NOTICE,
    getMinOrderReqNotice
  ),
];
