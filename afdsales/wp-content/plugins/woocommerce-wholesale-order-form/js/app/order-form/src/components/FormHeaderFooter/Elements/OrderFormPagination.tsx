import { Pagination } from "antd";
import { connect } from "react-redux";

declare var WWOF_Frontend_Options: any;

const OrderFormPagination = (props: any) => {
  const { orderFormId, orderFormData, styles, fetchProducts } = props;

  if (typeof orderFormData["formPagination"][orderFormId] === "undefined")
    return <></>;

  const searchInput =
    orderFormData?.formFilters?.[orderFormId]?.searchInput ?? "";

  const selectedCategory =
    orderFormData?.formFilters?.[orderFormId]?.selectedCategory ?? "";

  const activePage =
    orderFormData?.formPagination?.[orderFormId]?.active_page ?? 1;

  const totalProducts =
    parseInt(orderFormData?.formPagination?.[orderFormId]?.total_products) ?? 0;

  const productsPerPage =
    parseInt(orderFormData?.formPagination?.[orderFormId]?.per_page) || 0;

  return (
    <>
      <Pagination
        current={activePage}
        total={totalProducts}
        pageSize={productsPerPage}
        style={{ ...styles }}
        onChange={(active_page: number) => {
          try {
            // Focus Order Form Wrapper
            let elements = document.getElementsByClassName(
              "order-form-" + orderFormId
            );

            if (elements[0]) {
              elements[0].scrollIntoView({
                behavior: "smooth",
                inline: "nearest",
              });
            }

            let sort_by =
              orderFormData?.formTableSorter?.[orderFormId]?.sort_by ??
              orderFormData.formSettings[orderFormId]?.sort_by;

            let sort_order =
              orderFormData?.formTableSorter?.[orderFormId]?.sort_order ??
              orderFormData?.formSettings?.[orderFormId]?.sort_order;

            fetchProducts({
              orderFormData,
              sort_order,
              sort_by,
              search: searchInput,
              category: selectedCategory,
              active_page:
                active_page ??
                orderFormData?.formPagination?.[orderFormId]?.active_page,
              searching: "no",
              uid: WWOF_Frontend_Options.uid,
            });
          } catch (e) {
            console.log(e);
          }
        }}
        showSizeChanger={false}
      />
    </>
  );
};

const mapStateToProps = (store: any) => ({
  pagination: store.pagination,
  orderFormData: store.orderFormData,
  filter: store.filter,
});

export default connect(mapStateToProps)(OrderFormPagination);
