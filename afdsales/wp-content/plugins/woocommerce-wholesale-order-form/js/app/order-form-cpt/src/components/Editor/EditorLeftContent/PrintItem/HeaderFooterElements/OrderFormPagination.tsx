import { useEffect } from "react";
import { Pagination } from "antd";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { paginationActions, productActions } from "store/actions";

const { setPaginationState } = paginationActions;
const { fetchProducts } = productActions;

const OrderFormPagination = (props: any) => {
  const {
    orderForm,
    pagination,
    styles,
    productsPerPage,
    actions,
    getPropValue,
    properties,
  } = props;
  const { setPaginationState, fetchProducts } = actions;

  let productPerPage = getPropValue({
    properties,
    prop: "productsPerPage",
  });
  if (typeof productPerPage !== "number") productPerPage = 0;

  useEffect(() => {
    let params: any = { per_page: productsPerPage };
    if (typeof orderForm.settingsData.wwof_general_sort_order !== "undefined") {
      params = {
        ...params,
        sort_order: orderForm.settingsData.wwof_general_sort_order,
      };
    }

    if (productsPerPage > 0) {
      fetchProducts({
        per_page: productsPerPage,
      });

      setPaginationState({
        per_page: productsPerPage,
      });
    }
  }, [productsPerPage]);

  return (
    <>
      <Pagination
        current={pagination.active_page}
        total={parseInt(pagination.total_products)}
        pageSize={parseInt(productsPerPage) || 10}
        style={{ ...styles }}
        onChange={() => {}}
        showSizeChanger={false}
      />
    </>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  pagination: store.pagination,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setPaginationState,
      fetchProducts,
    },
    dispatch
  ),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(OrderFormPagination);
