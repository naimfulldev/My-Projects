// Redux
import { connect } from "react-redux";

const ProductCount = (props: any) => {
  const { properties, pagination, styles, productCountStrings } = props;
  const totalProducts = pagination.total_products || 0;
  const { products_str } = productCountStrings;
  return (
    <div
      style={{ ...styles }}
      {...properties}
      dangerouslySetInnerHTML={{
        __html: `${totalProducts} ${products_str}`,
      }}
    ></div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  productCountStrings: store.i18n.backend.product_count,
});

export default connect(mapStateToProps)(ProductCount);
