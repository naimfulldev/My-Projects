import { Button } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormDataActions } from "store/actions";

const { setFormFilters } = orderFormDataActions;

const SearchButton = (props: any) => {
  const {
    orderFormData,
    styles,
    orderFormId,
    fetchProducts,
    getPropValue,
    properties,
    actions,
    searchButtonStrings,
  } = props;

  const { setFormFilters } = actions;

  const onFormSubmit = (e: any) => {
    e.preventDefault();

    const searchInput =
      orderFormData?.["formFilters"]?.[orderFormId]?.["searchInput"] ?? "";

    const selectedCategory =
      orderFormData?.["formFilters"]?.[orderFormId]?.["selectedCategory"] ?? "";

    const activePage =
      orderFormData?.["formFilters"]?.[orderFormId]?.["active_page"] ?? 1;

    const allowSkuSearch =
      orderFormData?.["formFilters"]?.[orderFormId]?.["allowSkuSearch"] ?? 1;

    if (searchInput || selectedCategory) {
      fetchProducts({
        orderFormData,
        search: searchInput,
        category: selectedCategory,
        active_page: activePage,
        searching: "yes",
        allow_sku_search: allowSkuSearch,
      });
    }
  };

  const showAll = (e: any) => {
    e.preventDefault();

    let per_page = orderFormData?.formPagination?.[orderFormId]?.per_page ?? 10;

    fetchProducts({
      orderFormData,
      search: "",
      category: "",
      active_page: 1,
      per_page,
      searching: "no",
      sort_order: "",
      products: "",
      categories: "",
      show_all: true,
    });

    setFormFilters({
      [orderFormId]: {
        searchInput: "",
        selectedCategory: "",
      },
    });
  };

  let showClearButton = getPropValue({
    properties,
    prop: "showClearButton",
  });

  showClearButton =
    showClearButton === null || showClearButton === undefined
      ? true
      : showClearButton;

  let buttonColor = getPropValue({
    properties,
    prop: "buttonColor",
  });

  let buttonTextColor = getPropValue({
    properties,
    prop: "buttonTextColor",
  });

  const searchButtonElementStyle = {
    background: buttonColor,
    color: buttonTextColor,
    border: buttonColor,
  };

  return (
    <div
      className="search-and-clear-buttons"
      style={{ whiteSpace: "pre-wrap" }}
    >
      <Button
        size="large"
        type="primary"
        style={{ ...styles, ...searchButtonElementStyle }}
        onClick={(e: any) => onFormSubmit(e)}
      >
        {getPropValue({ properties, prop: "searchButtonText" }) ||
          searchButtonStrings?.search_btn}
      </Button>{" "}
      {showClearButton ? (
        <Button
          size="large"
          style={{ ...styles, width: "auto" }}
          onClick={(e: any) => showAll(e)}
        >
          {getPropValue({ properties, prop: "clearButtonText" }) ||
            searchButtonStrings?.clear_btn}
        </Button>
      ) : (
        ""
      )}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  searchButtonStrings: store.i18n.frontend.search_button,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setFormFilters,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(SearchButton);
