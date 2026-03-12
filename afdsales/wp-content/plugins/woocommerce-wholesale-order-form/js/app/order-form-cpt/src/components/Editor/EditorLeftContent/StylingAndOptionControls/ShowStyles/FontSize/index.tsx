import { useEffect, useState } from "react";
import { InputNumber, Radio } from "antd";
import { connect } from "react-redux";

const FontSize = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getStyleValue,
    fontSizeStrings,
  } = props;
  const [type, setType] = useState(
    getStyleValue({ styling, id, target, style: "fontSize", extra: "type" }) ||
      "inherit"
  );

  const [fontSize, setFontSize] = useState(
    getStyleValue({ styling, id, target, style: "fontSize", extra: "value" }) ||
      ""
  );

  useEffect(() => {
    setType(
      getStyleValue({
        styling,
        id,
        target,
        style: "fontSize",
        extra: "type",
      }) || "inherit"
    );

    setFontSize(
      getStyleValue({
        styling,
        id,
        target,
        style: "fontSize",
        extra: "value",
      }) || ""
    );
  }, [id]);

  return (
    <div className="font-size">
      <label htmlFor="font-size">{fontSizeStrings?.label}:</label>
      <InputNumber
        disabled={type === "inherit" ? true : false}
        style={{ width: 100 }}
        min={1}
        defaultValue={1}
        value={type === "inherit" ? "Inherit" : fontSize}
        onChange={(value: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              fontSize: {
                value,
                type,
              },
            },
          });
          setFontSize(value);
        }}
      />
      <Radio.Group
        onChange={(e) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              fontSize: {
                value: fontSize,
                type: e.target.value,
              },
            },
          });
          setType(e.target.value);
        }}
        value={type}
        style={{ display: "inline-flex" }}
      >
        <Radio value="inherit">{fontSizeStrings?.auto}</Radio>
        <Radio value="percentage">{fontSizeStrings?.percent}</Radio>
        <Radio value="pixels">{fontSizeStrings?.px}</Radio>
      </Radio.Group>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  fontSizeStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.font_size,
});

export default connect(mapStateToProps)(FontSize);
