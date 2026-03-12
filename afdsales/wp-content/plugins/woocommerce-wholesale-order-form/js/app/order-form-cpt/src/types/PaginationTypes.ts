// PAGINATION
export enum EPaginationActionTypes {
  SET_PAGINATION_STATE = "SET_PAGINATION_STATE",
}
export interface IPagination {
  active_page: number;
  per_page: number;
  total_products: number;
  total_page: number;
}
