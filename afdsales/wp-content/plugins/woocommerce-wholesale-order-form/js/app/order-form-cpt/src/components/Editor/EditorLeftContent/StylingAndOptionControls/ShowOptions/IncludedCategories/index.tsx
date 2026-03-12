import { useEffect, useState } from "react";
import { Select } from "antd";
import { connect } from "react-redux";

const { Option } = Select;

const IncludedCategories = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    productCategories,
    getCategoryList,
    includedCategoriesStrings,
  } = props;

  const { label } = includedCategoriesStrings;

  const [includedCategories, setIncludedCategories] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "includedCategories",
      extra: "",
    }) || []
  );

  const [options, setOptions] = useState<any>("");

  useEffect(() => {
    setIncludedCategories(
      getPropValue({
        styling,
        id,
        target,
        style: "includedCategories",
        extra: "",
      }) || []
    );
  }, [id]);

  useEffect(() => {
    if (productCategories !== undefined && productCategories.length > 0) {
      let categoryList = getCategoryList({ productCategories });
      let allOptions = categoryList.map((cat: any, index: number) => {
        return (
          <Option key={index} value={cat.value}>
            {cat.title}
          </Option>
        );
      });
      setOptions(allOptions);
    }
  }, [productCategories]);

  return (
    <div className="included-category">
      <label htmlFor="included-category">{label}:</label>
      <Select
        placeholder="None"
        mode="multiple"
        allowClear={true}
        style={{ width: "100%" }}
        defaultValue={includedCategories}
        onChange={(value: string) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              includedCategories: value,
            },
          });
          setIncludedCategories(value);
        }}
      >
        {options}
      </Select>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  productCategories: store.products.categories,
  includedCategoriesStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .included_categories,
});

export default connect(mapStateToProps)(IncludedCategories);
