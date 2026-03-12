import { useEffect, useState } from "react";
import { Popover, Select, Tooltip } from "antd";
import { InfoCircleOutlined } from "@ant-design/icons";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

import { orderFormActions } from "store/actions";

const { setOrderFormSettingsData } = orderFormActions;

const { Option } = Select;

const VariationSelectorStyle = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    actions,
    variationSelectorStyleStrings,
  } = props;

  const { setOrderFormSettingsData } = actions;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "variationSelectorStyle",
      extra: "",
    }) || "combo"
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "variationSelectorStyle",
        extra: "",
      }) || "combo"
    );
  }, [id]);

  useEffect(() => {
    if (value) {
      setOrderFormSettingsData({ variation_selector_style: value });
    }
  }, [value]);

  console.log();
  return (
    <div className="variation-selector">
      <label htmlFor="variation-selector">
        {variationSelectorStyleStrings?.label}:
        <Popover
          style={{ width: "100px" }}
          className="qty-restriction"
          content={
            <div
              style={{ width: "300px" }}
              dangerouslySetInnerHTML={{
                __html: variationSelectorStyleStrings?.popover_content,
              }}
            ></div>
          }
          title={variationSelectorStyleStrings?.popover_title}
          trigger="click"
        >
          <Tooltip title={variationSelectorStyleStrings?.tooltip_title}>
            <InfoCircleOutlined
              style={{ paddingLeft: "4px", paddingTop: "5px" }}
            />
          </Tooltip>
        </Popover>
      </label>
      <Select
        style={{ width: "100%" }}
        defaultValue={value || variationSelectorStyleStrings?.combo}
        value={value}
        onSelect={(val: string) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              variationSelectorStyle: val,
            },
          });
          setValue(val);
        }}
      >
        <Option value="combo">{variationSelectorStyleStrings?.combo}</Option>
        <Option value="standard">
          {variationSelectorStyleStrings?.standard}
        </Option>
      </Select>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  variationSelectorStyleStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .variation_selector_style,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setOrderFormSettingsData,
    },
    dispatch
  ),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(VariationSelectorStyle);
