import { useEffect, useState } from "react";
import { Tooltip } from "antd";
import {
  AlignLeftOutlined,
  AlignCenterOutlined,
  AlignRightOutlined,
} from "@ant-design/icons";
import { connect } from "react-redux";

const Alignment = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getStyleValue,
    label,
    section,
    alignmentStrings,
  } = props;

  const [alignment, setAlignment] = useState(
    getStyleValue({
      styling,
      id,
      target,
      style: "justifyContent",
      extra: "",
    })
  );

  useEffect(() => {
    setAlignment(
      getStyleValue({
        styling,
        id,
        target,
        style: "justifyContent",
        extra: "",
      })
    );
  }, [id]);

  useEffect(() => {
    if (alignment === null) {
      switch (section) {
        case "formHeaderFooter":
          setAlignment("flex-start");
          break;
        case "formTable":
          setAlignment("center");
          break;
        case "formTableSetting":
          setAlignment("flex-start");
          break;
      }
    }
  }, [section, alignment]);

  return (
    <div className="alignment">
      <label htmlFor="alignment">
        {label ? label : alignmentStrings?.element_alignment}:
      </label>
      <Tooltip title={alignmentStrings?.["left"] ?? "Left"}>
        <AlignLeftOutlined
          style={{ color: alignment === "flex-start" ? "#0071a1" : "" }}
          onClick={() => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: { justifyContent: "flex-start" },
            });
            setAlignment("flex-start");
          }}
        />
      </Tooltip>
      <Tooltip title={alignmentStrings?.["center"] ?? "Center"}>
        <AlignCenterOutlined
          style={{ color: alignment === "center" ? "#0071a1" : "" }}
          onClick={() => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: { justifyContent: "center" },
            });
            setAlignment("center");
          }}
        />
      </Tooltip>
      <Tooltip title={alignmentStrings?.["right"] ?? "Right"}>
        <AlignRightOutlined
          style={{ color: alignment === "flex-end" ? "#0071a1" : "" }}
          onClick={() => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: { justifyContent: "flex-end" },
            });
            setAlignment("flex-end");
          }}
        />
      </Tooltip>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  alignmentStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.alignment,
});

export default connect(mapStateToProps)(Alignment);
