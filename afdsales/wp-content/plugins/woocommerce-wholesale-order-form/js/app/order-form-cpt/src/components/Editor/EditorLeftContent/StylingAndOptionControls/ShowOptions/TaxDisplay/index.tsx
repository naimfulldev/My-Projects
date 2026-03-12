import { useEffect, useState } from "react";
import { Select } from "antd";
import { connect } from "react-redux";

const { Option } = Select;

const TaxDisplay = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    taxDisplayStrings,
  } = props;

  const [taxDisplay, setTaxDisplayValue] = useState(
    getPropValue({ styling, id, target, style: "taxDisplay", extra: "" }) || ""
  );

  useEffect(() => {
    setTaxDisplayValue(
      getPropValue({
        styling,
        id,
        target,
        style: "taxDisplay",
        extra: "",
      }) || ""
    );
  }, [id]);

  let extraProps = {};

  if (typeof taxDisplay !== "undefined" && taxDisplay.length > 0) {
    extraProps = {
      defaultValue: taxDisplay,
    };
  }

  return (
    <div className="tax-display">
      <label htmlFor="tax-display">{taxDisplayStrings?.label}:</label>
      <Select
        {...extraProps}
        style={{ width: "100%" }}
        allowClear={true}
        placeholder={taxDisplayStrings?.wc_default}
        onChange={(value) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              taxDisplay: value,
            },
          });
          setTaxDisplayValue(value);
        }}
      >
        <Option value="incl">{taxDisplayStrings?.incl}</Option>
        <Option value="excl">{taxDisplayStrings?.excl}</Option>
      </Select>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  taxDisplayStrings:
    store.i18n.backend.styling_and_option_controls.show_options.tax_display,
});

export default connect(mapStateToProps)(TaxDisplay);
