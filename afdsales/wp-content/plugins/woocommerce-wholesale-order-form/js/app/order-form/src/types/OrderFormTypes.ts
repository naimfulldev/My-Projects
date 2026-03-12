export enum EOrderFormActionTypes {
  FETCH_PRODUCTS = "FETCH_PRODUCTS",
  FETCH_CATEGORIES = "FETCH_CATEGORIES",
  SET_APP_STATE = "SET_APP_STATE",
  SET_CATEGORIES = "SET_CATEGORIES",
  LOAD_MORE_VARIATIONS = "LOAD_MORE_VARIATIONS",
  LOAD_MORE_PRODUCTS = "LOAD_MORE_PRODUCTS",
  SET_SHOW_MODAL = "SET_SHOW_MODAL",
}

export interface IOrderFormAction {
  categories: any[];
  cartURL: string;
  showModal: boolean;
  modalProps: {
    orderFormId: number;
    onClickAction: string;
    product: object[];
  };
  attributes: {
    show_search: string;
    products: any[];
    categories: any[];
  };
}
