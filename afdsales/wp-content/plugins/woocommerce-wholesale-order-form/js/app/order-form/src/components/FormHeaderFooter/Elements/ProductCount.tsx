import { connect } from "react-redux";

const ProductCount = (props: any) => {
  const { orderFormData, styles, orderFormId, productCountStrings } = props;

  if (typeof orderFormData?.formPagination?.[orderFormId] === "undefined")
    return <></>;

  const productCount =
    orderFormData?.formPagination?.[orderFormId]?.total_products || 0;

  return (
    <div
      className="product-count"
      style={{ ...styles }}
      dangerouslySetInnerHTML={{
        __html: `${productCount} ${productCountStrings?.products}`,
      }}
    ></div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  productCountStrings: store.i18n.frontend.product_count,
});

export default connect(mapStateToProps)(ProductCount);
