import { useState, useEffect } from "react";
import { InputNumber, Radio } from "antd";
import { connect } from "react-redux";

const Width = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getStyleValue,
    label,
    section,
    widthStrings,
  } = props;

  const [type, setType] = useState(
    getStyleValue({ styling, id, target, style: "width", extra: "type" })
  );

  const [width, setWidth] = useState(
    getStyleValue({ styling, id, target, style: "width", extra: "value" })
  );

  useEffect(() => {
    if (section === "formTableSetting") {
      setType(
        getStyleValue({ styling, id, target, style: "width", extra: "type" }) ||
          "full-width"
      );
    } else {
      setType(
        getStyleValue({ styling, id, target, style: "width", extra: "type" }) ||
          "auto"
      );
    }

    let boxWidth = getStyleValue({
      styling,
      id,
      target,
      style: "width",
      extra: "value",
    });

    setWidth(boxWidth);

    if (
      styling.item.type === "ROW" ||
      (styling.item.type === "ITEM" && target === "box")
    ) {
      if (type === "percentage" && boxWidth > 100) {
        setWidth(100);
      } else if (type === "pixels" && boxWidth === null) {
        setWidth(1000);
      }
    }
  }, [styling.item.id, type]);

  useEffect(() => {
    if (type === null || typeof type === "undefined") {
      switch (section) {
        case "formHeaderFooter":
        case "formTable":
          setType("auto");
          break;
        case "formTableSetting":
          setType("full-width");
          break;
      }
    }
  }, [section, type]);

  return (
    <div className="width">
      <label htmlFor="width">
        {label
          ? label
          : target === "box"
          ? widthStrings?.box_width
          : widthStrings?.element_width}
        :
      </label>
      {section !== "formTableSetting" ? (
        <InputNumber
          disabled={type === "auto" ? true : false}
          style={{ width: 100 }}
          value={type === "auto" ? "Auto" : width}
          onChange={(value: any) => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: {
                width: {
                  value,
                  type,
                },
              },
            });
            setWidth(value);
          }}
        />
      ) : (
        ""
      )}
      <Radio.Group
        onChange={(e) => {
          let widthType = e.target.value;
          let widthValue = 0;

          if (styling.item.type === "ROW") {
            if (widthType === "percentage") {
              widthValue = 100;
            } else if (widthType === "pixels") {
              widthValue = 1000;
            }
          } else if (styling.item.type === "ITEM") {
            if (widthType === "percentage") {
              widthValue = 100;
            } else if (widthType === "pixels") {
              if (target === "box") widthValue = 500;
              else widthValue = 300;
            }
          } else {
            if (widthType === "percentage") {
              widthValue = 100;
            } else if (widthType === "pixels") {
              widthValue = 150;
            }
          }

          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              width: {
                value: widthValue,
                type: widthType,
              },
            },
          });
          setType(widthType);
        }}
        value={type}
        style={{ display: "inline-flex" }}
      >
        {section === "formTableSetting" ? (
          <>
            <Radio value="full-width">{widthStrings?.full_width}</Radio>
            <Radio value="auto">{widthStrings?.auto}</Radio>
          </>
        ) : (
          <>
            <Radio value="auto">{widthStrings?.auto}</Radio>
            <Radio value="percentage">{widthStrings?.percent}</Radio>
            <Radio value="pixels">{widthStrings?.px}</Radio>
          </>
        )}
      </Radio.Group>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  widthStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.width,
});

export default connect(mapStateToProps)(Width);
