import { useEffect, useState } from "react";
import { SketchPicker } from "react-color";
import { connect } from "react-redux";

const ButtonTextColor = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getStyleValue,
    buttonTextColorStrings,
  } = props;

  const [showColorPicker, setShowColorPicker] = useState(false);
  const [color, setColor] = useState(
    getStyleValue({
      styling,
      id,
      target,
      style: "buttonTextColor",
      extra: "",
    }) || "#fff"
  );

  useEffect(() => {
    setColor(
      getStyleValue({
        styling,
        id,
        target,
        style: "buttonTextColor",
        extra: "",
      }) || "#fff"
    );
  }, [id]);

  return (
    <div className="button-text-color">
      <label htmlFor="button-text-color">{buttonTextColorStrings?.label}</label>
      <div
        className="wrapper"
        style={{
          padding: "5px",
          background: "#fff",
          borderRadius: "1px",
          boxShadow: "0 0 0 1px rgba(0,0,0,.1)",
          display: "inline-block",
          cursor: "pointer",
        }}
        onClick={() => setShowColorPicker(!showColorPicker)}
      >
        <div
          style={{
            width: "36px",
            height: "14px",
            borderRadius: "2px",
            background: `${color}`,
          }}
        />
      </div>
      {showColorPicker ? (
        <div
          style={{
            position: "absolute",
            zIndex: 2,
          }}
        >
          <SketchPicker
            color={color}
            onChange={(color) => {
              updateStyling({
                setStyles,
                styling,
                id,
                target,
                toUpdate: {
                  buttonTextColor: color.hex,
                },
              });
              setColor(color.hex);
            }}
          />
        </div>
      ) : null}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  buttonTextColorStrings:
    store.i18n.backend.styling_and_option_controls.show_styles
      .button_text_color,
});

export default connect(mapStateToProps)(ButtonTextColor);
