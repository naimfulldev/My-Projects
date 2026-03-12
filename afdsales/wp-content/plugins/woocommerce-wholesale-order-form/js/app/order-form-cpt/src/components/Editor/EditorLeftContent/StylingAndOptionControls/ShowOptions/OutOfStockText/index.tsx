import { useEffect, useState } from "react";
import { Input } from "antd";
import { connect } from "react-redux";

const OutOfStockText = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    outOfStockStrings,
  } = props;

  const { label, default_value } = outOfStockStrings;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "outOfStockText",
      extra: "",
    }) || default_value
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "outOfStockText",
        extra: "",
      }) || default_value
    );
  }, [id]);

  return (
    <div className="out-of-stock-text">
      <label htmlFor="out-of-stock-text">{label}:</label>
      <Input
        placeholder={label}
        value={value ? value : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              outOfStockText: value,
            },
          });
          setValue(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  outOfStockStrings:
    store.i18n.backend.styling_and_option_controls.show_options.out_of_stock,
});

export default connect(mapStateToProps)(OutOfStockText);
