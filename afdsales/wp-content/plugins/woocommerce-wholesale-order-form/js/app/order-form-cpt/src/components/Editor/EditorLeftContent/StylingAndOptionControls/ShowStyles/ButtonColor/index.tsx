import { useEffect, useState } from "react";
import { SketchPicker } from "react-color";
import { connect } from "react-redux";

const ButtonColor = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getStyleValue,
    buttonColorStrings,
  } = props;

  const [showColorPicker, setShowColorPicker] = useState(false);
  const [color, setColor] = useState(
    getStyleValue({
      styling,
      id,
      target,
      style: "buttonColor",
      extra: "",
    }) || "#fff"
  );

  useEffect(() => {
    setColor(
      getStyleValue({
        styling,
        id,
        target,
        style: "buttonColor",
        extra: "",
      }) || "#fff"
    );
  }, [id]);

  return (
    <div className="button-color">
      <label htmlFor="button-color">{buttonColorStrings?.label}:</label>
      <div
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
                  buttonColor: color.hex,
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
  buttonColorStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.button_color,
});

export default connect(mapStateToProps)(ButtonColor);
