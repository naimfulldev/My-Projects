import { EProductListActionTypes } from "types/ProductListTypes";

export const productListActions = {
  setProductListColumns: (payload: any) => ({
    type: EProductListActionTypes.SET_PRODUCT_LIST_COLUMNS,
    payload,
  }),
  setProductQuantity: (payload: any) => ({
    type: EProductListActionTypes.SET_PRODUCT_QUANTITY,
    payload,
  }),
  setSelectedProducts: (payload: any) => ({
    type: EProductListActionTypes.SET_SELECTED_PRODUCTS,
    payload,
  }),
  setSelectedVariations: (payload: any) => ({
    type: EProductListActionTypes.SET_SELECTED_VARIATIONS,
    payload,
  }),
  addProductToCartAction: (payload: any) => ({
    type: EProductListActionTypes.ADD_PRODUCT_TO_CART,
    payload,
  }),
  addProductsToCartAction: (payload: any) => ({
    type: EProductListActionTypes.ADD_PRODUCTS_TO_CART,
    payload,
  }),
};
