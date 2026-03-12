import { useEffect, useState } from "react";
import { Select } from "antd";
import { connect } from "react-redux";

const { Option } = Select;

const ComboVariationsDropdown = (props: any) => {
  const {
    orderForm,
    products,
    product,
    variations,
    loadMoreVariations,
    comboVariationStrings,
  } = props;

  const { placeholder, no_results, loading_text } = comboVariationStrings;

  const [loading, setLoading] = useState(false);
  const [variationOptions, setVariationOptions] = useState<JSX.Element>();
  const [selectProps, setSelectProps] = useState({});

  useEffect(() => {
    if (variations.length <= 0 || typeof variations[product.id] === "undefined")
      return;

    let totalVariations = variations[product.id].length;

    if (
      typeof products.lazy_load_variations_data[product.id] !== "undefined" &&
      typeof products.lazy_load_variations_data[product.id].total_variations !==
        "undefined"
    )
      totalVariations =
        products.lazy_load_variations_data[product.id].total_variations;

    const dropdownOptions = variations[product.id].map(
      (variation: any, j: number) => {
        if (j === 0) {
          setSelectProps({ value: variation.id });
        }
        const totalAttr = variation.attributes.length;
        const name = variation.attributes.map((attribute: any, k: number) => {
          return (
            <span key={k}>
              {`${k === 0 ? "(" + (j + 1) + "/" + totalVariations + ") " : ""}`}
              {`${attribute.name}: ${attribute.option}`}
              {`${totalAttr !== k + 1 ? ", " : ""}`}
            </span>
          );
        });
        return (
          <Option key={j} value={variation.id} className="combo-variation">
            {name}
          </Option>
        );
      }
    );

    setVariationOptions(dropdownOptions);
  }, [variations, products]);

  const lazyLoading = (event: any) => {
    var target = event.target;

    if (
      !loading &&
      target.scrollTop + target.offsetHeight === target.scrollHeight
    ) {
      try {
        let nextPage =
          products.lazy_load_variations_data[product.id].current_page + 1;

        // Make sure not to go over the limit
        if (
          nextPage <= products.lazy_load_variations_data[product.id].total_page
        ) {
          setLoading(true);
          loadMoreVariations({
            orderForm,
            products,
            product_id: product.id,
            current_page:
              products.lazy_load_variations_data[product.id].current_page + 1,
            successCB: () => {
              setLoading(false);
            },
            failCB: () => {
              setLoading(false);
            },
          });
        }
      } catch (e) {
        console.log(e);
      }
    }
  };

  if (product.type !== "variable" || !variationOptions) return <></>;
  else
    return (
      <Select
        // defaultvalue={defaultvalue}
        size="large"
        placeholder={placeholder}
        style={{ textAlign: "left", maxWidth: "250px" }}
        filterOption={false}
        notFoundContent={no_results}
        allowClear={true}
        {...selectProps}
        onPopupScroll={lazyLoading}
      >
        {!loading
          ? variationOptions
          : [
              variationOptions,
              <Option value="loading" key="loading">
                {loading_text}
              </Option>,
            ]}
      </Select>
    );
};

const mapStateToProps = (store: any) => ({
  comboVariationStrings:
    store.i18n.backend.table_elements.variation_dropdown.combo,
});

export default connect(mapStateToProps)(ComboVariationsDropdown);
