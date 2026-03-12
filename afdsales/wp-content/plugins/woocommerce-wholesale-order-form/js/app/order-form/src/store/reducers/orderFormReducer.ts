import { EOrderFormActionTypes, IOrderFormAction } from "types/OrderFormTypes";

const defaults = {
  categories: [],
  cartURL: "",
  showModal: false,
  modalProps: {
    orderFormId: 0,
    product: [],
    onClickAction: "",
  },
  attributes: {
    show_search: "",
    products: [],
    categories: [],
  },
};

export default function orderFormReducer(
  state: IOrderFormAction = defaults,
  action: any
) {
  switch (action.type) {
    case EOrderFormActionTypes.SET_APP_STATE:
      const { cart_url, attributes } = action.payload.data;

      return {
        ...state,
        cartURL: cart_url,
        attributes,
      };

    case EOrderFormActionTypes.SET_CATEGORIES:
      return {
        ...state,
        categories: action.payload.categories,
      };

    case EOrderFormActionTypes.SET_SHOW_MODAL:
      return {
        ...state,
        showModal: action.payload.showModal,
        modalProps: {
          ...state.modalProps,
          ...action.payload.modalProps,
        },
      };

    default:
      return state;
  }
}
