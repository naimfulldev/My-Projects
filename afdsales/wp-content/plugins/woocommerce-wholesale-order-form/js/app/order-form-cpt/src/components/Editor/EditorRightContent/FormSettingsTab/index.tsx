import React, { useEffect } from "react";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import {
  Form,
  Radio,
  Checkbox,
  InputNumber,
  Select,
  Popover,
  Tooltip,
} from "antd";
import { LoadingOutlined } from "@ant-design/icons";
import { InfoCircleOutlined } from "@ant-design/icons";

// Actions
import { orderFormActions } from "store/actions";
import ProductsPerPage from "./ProductsPerPage";

const { getOrderFormSettings, setOrderFormSettingsData } = orderFormActions;

interface ISetSettings {
  type: string;
  key: string;
  value?: string;
  event?: React.SyntheticEvent;
}

const DisplayOption = (props: any) => {
  const {
    item,
    settingsData,
    setOrderFormSettingsData,
    formSettingsTabStrings,
  } = props;

  const { additional_note, more_info } = formSettingsTabStrings;

  const setSettingState = (props: ISetSettings) => {
    const { type, key, value, event } = props;

    if (type === "checkbox" && typeof event !== "undefined") {
      let target = event.target as HTMLInputElement;

      setOrderFormSettingsData({
        [key]: target.checked,
      });
    } else {
      setOrderFormSettingsData({
        [key]: value,
      });
    }
  };

  switch (item.type) {
    case "radio":
      return (
        <>
          <Form.Item
            className={item.id}
            style={{ whiteSpace: "unset" }}
            name={item.id}
            label={item.title}
          >
            <Radio.Group>
              {Object.keys(item["options"]).map((data: any, index: any) => {
                return (
                  <Radio key={index} value={data}>
                    {item["options"][data]}
                  </Radio>
                );
              })}
            </Radio.Group>
          </Form.Item>
        </>
      );
    case "checkbox":
      return (
        <>
          <Form.Item name={item.id} label={item.title}>
            <>
              <Checkbox
                checked={settingsData[item.id] || false}
                onChange={(event: any) =>
                  setSettingState({
                    type: "checkbox",
                    key: item.id,
                    event,
                  })
                }
              >
                {item.desc}{" "}
                {typeof item.note !== "undefined" ? (
                  <Popover
                    className="qty-restriction"
                    content={() => (
                      <p
                        style={{ width: "300px" }}
                        dangerouslySetInnerHTML={{ __html: item.note }}
                      />
                    )}
                    title={additional_note}
                    trigger="click"
                  >
                    <Tooltip title={more_info}>
                      <InfoCircleOutlined />
                    </Tooltip>
                  </Popover>
                ) : (
                  ""
                )}
              </Checkbox>
              {/* Lazy Loading Products Per page */}
              {item.id === "lazy_loading" ? <ProductsPerPage /> : ""}
            </>
          </Form.Item>
        </>
      );
    case "number":
      return (
        <>
          <Form.Item label={item.title}>
            <InputNumber />
          </Form.Item>
        </>
      );
    case "select":
      return (
        <>
          <Form.Item label={item.title}>
            <Select
              placeholder="WooCommerce default"
              allowClear={true}
              defaultValue={settingsData[item.id] || item.default}
              onChange={(value: any) =>
                setSettingState({
                  type: "select",
                  key: item.id,
                  value,
                })
              }
            >
              {Object.keys(item["options"]).map((data: any, index: any) => {
                return (
                  <Select.Option key={index} value={data}>
                    {item["options"][data]}
                  </Select.Option>
                );
              })}
            </Select>
          </Form.Item>
        </>
      );
    case "wwof_image_dimension":
      return (
        <>
          <Form.Item label={item.title}>
            <InputNumber value={item["default"].width} />x
            <InputNumber value={item["default"].height} />
            px
          </Form.Item>
        </>
      );
    default:
      return <></>;
  }
};

const DisplaySettings = (props: any) => {
  const { settings } = props;

  const options = Object.keys(settings).map((index: any) => {
    const item = settings[index];

    return <DisplayOption item={item} key={index} {...props} />;
  });

  return Object.values(settings).length > 0 ? (
    <>{options}</>
  ) : (
    <LoadingOutlined />
  );
};

const FormSettingsTab = (props: any) => {
  const { orderForm, formSettingsTabStrings } = props;
  const { getOrderFormSettings, setOrderFormSettingsData } = props.actions;

  const propsToPass = {
    settingsData: orderForm.settingsData,
    setOrderFormSettingsData,
    settings: orderForm.settings,
    orderForm,
    formSettingsTabStrings,
  };

  useEffect(() => {
    getOrderFormSettings();
  }, []);

  return <DisplaySettings {...propsToPass} />;
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  formSettingsTabStrings: store.i18n.backend.form_settings_tab,
});
const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      getOrderFormSettings,
      setOrderFormSettingsData,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(FormSettingsTab);
