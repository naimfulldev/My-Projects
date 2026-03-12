// PRODUCTS
export enum EProductActionTypes {
  SET_FETCHING_PRODUCTS = "SET_FETCHING_PRODUCTS",
  FETCH_PRODUCTS = "FETCH_PRODUCTS",
  SET_PRODUCTS = "SET_PRODUCTS",
  SET_CATEGORIES = "SET_CATEGORIES",
  FETCH_CATEGORIES = "FETCH_CATEGORIES",
  SET_VARIATIONS = "SET_VARIATIONS",
  SET_LAZY_LOAD_DATA = "SET_LAZY_LOAD_DATA",
  LOAD_MORE_VARIATIONS = "LOAD_MORE_VARIATIONS",
}

export interface IProducts {
  fetchingProducts: boolean;
  products: any[];
  categories: any[];
  variations: any[];
}
