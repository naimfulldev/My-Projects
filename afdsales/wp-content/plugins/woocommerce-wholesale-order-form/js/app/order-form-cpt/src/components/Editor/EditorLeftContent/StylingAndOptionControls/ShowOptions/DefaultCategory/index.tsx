import { useEffect, useState } from "react";
import { Select } from "antd";
import { connect } from "react-redux";

const { Option } = Select;

const DefaultCategory = (props: any) => {
  const {
    styling,
    id,
    target,
    getPropValue,
    productCategories,
    updateStyling,
    setStyles,
    getCategoryList,
    defaultCategoryStrings,
  } = props;

  const [defaultValue, setDefaultValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "defaultCategory",
      extra: "",
    }) || []
  );

  const [options, setOptions] = useState<any>("");

  useEffect(() => {
    setDefaultValue(
      getPropValue({
        styling,
        id,
        target,
        style: "defaultCategory",
        extra: "",
      })
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
    <div className="default-category">
      <label htmlFor="default-category">{defaultCategoryStrings?.label}:</label>
      <Select
        showSearch={true}
        placeholder="None"
        allowClear={true}
        style={{ width: "100%" }}
        defaultValue={defaultValue}
        onChange={(value: string) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              defaultCategory: value,
            },
          });
          setDefaultValue(value);
        }}
      >
        {options}
      </Select>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  productCategories: store.products.categories,
  defaultCategoryStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .default_category,
});

export default connect(mapStateToProps)(DefaultCategory);
