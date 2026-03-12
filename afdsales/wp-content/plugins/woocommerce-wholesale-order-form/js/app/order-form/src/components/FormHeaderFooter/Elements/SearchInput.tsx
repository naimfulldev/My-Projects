import { ChangeEvent, useEffect, useState } from "react";
import { Input } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormDataActions } from "store/actions";

const { setFormFilters } = orderFormDataActions;

const SearchInput = (props: any) => {
  const {
    getPropValue,
    properties,
    orderFormId,
    styles,
    fetchProducts,
    submitOnEnter,
    allowSkuSearch,
    orderFormData,
    actions,
    searchInputStrings,
  } = props;

  const { setFormFilters } = actions;

  const [searchInput, setSearchInput] = useState<string>("");

  const placeholder =
    getPropValue({ properties, prop: "placeholder" }) ??
    searchInputStrings?.placeholder;

  useEffect(() => {
    setFormFilters({
      [orderFormId]: {
        ...orderFormData["formFilters"][orderFormId],
        allowSkuSearch,
      },
    });
  }, [allowSkuSearch]);

  useEffect(() => {
    const searchInput =
      orderFormData?.["formFilters"]?.[orderFormId]?.["searchInput"] ?? "";

    setSearchInput(searchInput);
  }, [orderFormData["formFilters"][orderFormId]]);

  const onTextEnter = (e: any) => {
    e.preventDefault();

    const selectedCategory =
      orderFormData?.formFilters?.[orderFormId]?.selectedCategory ?? "";

    if (submitOnEnter) {
      fetchProducts({
        orderFormData,
        search: e.target.value,
        category: selectedCategory,
        active_page: 1,
        searching: "yes",
        allow_sku_search: allowSkuSearch,
      });
    }
  };

  return (
    <>
      <Input
        size="large"
        placeholder={placeholder}
        style={{ width: "250px", ...styles }}
        onChange={(e: ChangeEvent<HTMLInputElement>) => {
          setSearchInput(e.target.value);
          setFormFilters({
            [orderFormId]: {
              ...orderFormData["formFilters"][orderFormId],
              searchInput: e.target.value,
            },
          });
        }}
        value={searchInput}
        onPressEnter={(e) => onTextEnter(e)}
      />
    </>
  );
};

const mapStateToProps = (store: any) => ({
  orderFormData: store.orderFormData,
  filter: store.filter,
  searchInputStrings: store.i18n.frontend.search_input,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setFormFilters,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(SearchInput);
