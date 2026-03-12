import { useEffect, useState } from "react";
import { Checkbox } from "antd";
// Redux
import { connect } from "react-redux";

const DecimalQuantity = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    decimalQuantityStrings,
  } = props;

  const { label, description } = decimalQuantityStrings;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "decimalQuantity",
      extra: "",
    })
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "decimalQuantity",
        extra: "",
      })
    );
  }, [id]);

  return (
    <div className="decimal-quantity">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              decimalQuantity: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {label}
      </Checkbox>
      <em>{description}</em>
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  decimalQuantityStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .decimal_quantity,
});

export default connect(mapStateToProps)(DecimalQuantity);
