import { Tag } from "antd";
import { connect } from "react-redux";
import { htmlDecode } from "helpers/htmlDecode";

declare var WWOF_Frontend_Options: any;

const Categories = (props: any) => {
  const { product, categoriesStrings } = props;

  if (product.categories.length <= 0) return <></>;

  const tags = product.categories.map((cat: any, key: number) => {
    return (
      <Tag
        style={{ cursor: "pointer", padding: "4px 6px" }}
        key={key}
        onClick={() => {
          window.location.href =
            WWOF_Frontend_Options.site_url +
            "/" +
            WWOF_Frontend_Options.category_base +
            "/" +
            cat.slug;
        }}
      >
        {htmlDecode(cat.name)}
      </Tag>
    );
  });

  return (
    <div className="categories">
      <span>{categoriesStrings?.category}:</span> {tags}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  categoriesStrings: store.i18n.frontend.product_modal,
});

export default connect(mapStateToProps)(Categories);
