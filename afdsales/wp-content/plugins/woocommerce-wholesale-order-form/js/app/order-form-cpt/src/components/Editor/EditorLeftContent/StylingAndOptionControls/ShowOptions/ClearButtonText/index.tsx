import { useEffect, useState } from "react";
import { Input } from "antd";

// Redux
import { connect } from "react-redux";

const ClearButtonText = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    clearButtonTextStrings,
  } = props;
  const { text, label } = clearButtonTextStrings;

  const [buttonTextValue, setButtonTextValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "clearButtonText",
      extra: "",
    }) || ""
  );

  useEffect(() => {
    setButtonTextValue(
      getPropValue({
        styling,
        id,
        target,
        style: "clearButtonText",
        extra: "",
      }) || text
    );
  }, [id]);

  return (
    <div className="clear-button-text">
      <label htmlFor="clear-button-text">{label}:</label>
      <Input
        placeholder={label}
        value={buttonTextValue ? buttonTextValue : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              clearButtonText: value,
            },
          });
          setButtonTextValue(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  clearButtonTextStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .clear_button_text,
});

export default connect(mapStateToProps)(ClearButtonText);
