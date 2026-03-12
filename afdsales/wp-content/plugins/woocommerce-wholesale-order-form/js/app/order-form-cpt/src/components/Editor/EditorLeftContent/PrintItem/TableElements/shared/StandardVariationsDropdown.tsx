import { Select } from "antd";
import { connect } from "react-redux";
const { Option } = Select;

const StandardVariationsDropdown = (props: any) => {
  const { product, variations, standardVariationStrings } = props;

  const { placeholder, no_results } = standardVariationStrings;

  if (typeof variations[product.id] === "undefined") return <></>;

  let variationData: any = {};
  let variationDataMaster: any = {};
  let variationsList = variations[product.id];

  for (const [i, variation] of variationsList.entries()) {
    const attributes = variation.attributes;
    let attrKey = "";
    for (const [j, attr] of attributes.entries()) {
      attrKey += attr.name + attr.option;
      variationData[attr.name] = {
        ...variationData[attr.name],
        [attr.option]: attr.option,
      };
    }
    variationDataMaster[attrKey] = { id: variation.id };
  }

  variationData = Object.entries(variationData);

  const rows = variationData.map((variation: any) => {
    let label = variation[0];
    variation = Object.entries(variation[1]);
    let defaultValue = "";
    const options = variation.map((attribute: any, i: number) => {
      if (i === 0) defaultValue = attribute[0];
      return (
        <Option
          value={attribute[0]}
          key={attribute[0]}
          className="combo-variation"
        >
          {attribute[0]}
        </Option>
      );
    });

    return (
      <div
        key={label}
        style={{ margin: "4px 0px" }}
        className="standard-dropdown"
      >
        <label htmlFor={label}>{label}: </label>
        <Select
          defaultValue={defaultValue}
          size="large"
          placeholder={placeholder}
          style={{ textAlign: "left", maxWidth: "250px" }}
          filterOption={false}
          notFoundContent={no_results}
          allowClear={true}
        >
          {options}
        </Select>
      </div>
    );
  });

  return <>{rows}</>;
};

const mapStateToProps = (store: any) => ({
  standardVariationStrings:
    store.i18n.backend.table_elements.variation_dropdown.standard,
});

export default connect(mapStateToProps)(StandardVariationsDropdown);
