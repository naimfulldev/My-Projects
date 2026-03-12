import { useEffect, useState } from "react";
import { Select } from "antd";
import { connect } from "react-redux";

const { Option } = Select;

const ImageClickAction = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    imageCLickActionStrings,
  } = props;

  const {
    label,
    show_product_details_propup,
    navigate_to_product_page,
    show_image_popup,
  } = imageCLickActionStrings;

  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "onClick", extra: "" }) ||
      "show-product-details"
  );

  useEffect(() => {
    setValue(
      getPropValue({ styling, id, target, style: "onClick", extra: "" }) ||
        "show-product-details"
    );
  }, [id]);

  let columnName = "";
  switch (styling.item.id) {
    case "product-image":
      columnName = "Image";
      break;
    case "product-name":
      columnName = "Product Name";
      break;
  }
  return (
    <div className="click-action">
      <label htmlFor="click-action">
        {columnName} {label}:
      </label>
      <Select
        style={{ width: "100%" }}
        defaultValue={value || show_product_details_propup}
        value={value}
        onSelect={(val: string) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              onClick: val,
            },
          });
          setValue(val);
        }}
      >
        <Option value="show-product-details">
          {show_product_details_propup}
        </Option>
        <Option value="navigate-to-product-page">
          {navigate_to_product_page}
        </Option>
        {styling.item.itemId !== "product-name" ? (
          <Option value="show-image-popup">{show_image_popup}</Option>
        ) : (
          ""
        )}
      </Select>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  imageCLickActionStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .image_click_action,
});

export default connect(mapStateToProps)(ImageClickAction);
