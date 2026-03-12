import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

declare var Options: any;

const WholesaleQuantityBasedPricingTable = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    wholesaleQuantityBasedStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "showQuantityBasedPricing",
      extra: "",
    })
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "showQuantityBasedPricing",
        extra: "",
      })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  if (Options.wwpp_active === "") {
    return <></>;
  } else {
    return (
      <div className="wholesale-quantity-based-pricing-table">
        <Checkbox
          checked={value}
          onChange={(e: any) => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: {
                showQuantityBasedPricing: e.target.checked,
              },
            });
            setValue(e.target.checked);
          }}
        >
          {wholesaleQuantityBasedStrings?.label}
        </Checkbox>
        <em>{wholesaleQuantityBasedStrings?.description}</em>
      </div>
    );
  }
};

const mapStateToProps = (store: any) => ({
  wholesaleQuantityBasedStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .wholesale_quantity_based,
});
export default connect(mapStateToProps)(WholesaleQuantityBasedPricingTable);
