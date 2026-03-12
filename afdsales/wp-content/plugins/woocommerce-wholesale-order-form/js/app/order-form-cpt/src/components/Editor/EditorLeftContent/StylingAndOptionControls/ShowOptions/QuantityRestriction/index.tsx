import { useEffect, useState } from "react";
import { Checkbox, Popover, Tooltip } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";

import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

import { orderFormActions } from "store/actions";

const { setQuantityRestriction } = orderFormActions;

// This variable is loaded in wp wp_enqueue_scripts via wp_localize_script
declare var Options: { wwpp_active: string };

const QuantityRestriction = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    quantityRestrictionStrings,
  } = props;

  const { label, popover_title, popover_content, tooltip_title } =
    quantityRestrictionStrings;

  const { setQuantityRestriction } = props.actions;
  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "quantityRestriction",
      extra: "",
    })
  );

  useEffect(() => {
    setQuantityRestriction(value);
  }, [value]);

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "quantityRestriction",
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
      <div className="qty-restriction">
        <Checkbox
          checked={value}
          onChange={(e: any) => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: {
                quantityRestriction: e.target.checked,
              },
            });
            setValue(e.target.checked);
          }}
        >
          {label} &nbsp;
          <Popover
            style={{ width: "100px" }}
            className="qty-restriction"
            content={
              <div
                style={{ width: "500px" }}
                dangerouslySetInnerHTML={{ __html: popover_content }}
              />
            }
            title={popover_title}
            trigger="click"
          >
            <Tooltip title={tooltip_title}>
              <InfoCircleOutlined />
            </Tooltip>
          </Popover>
        </Checkbox>
      </div>
    );
  }
};

const mapStateToProps = (store: any) => ({
  quantityRestrictionStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .quantity_restriction,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({ setQuantityRestriction }, dispatch),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(QuantityRestriction);
