import { useEffect, useState } from "react";
import { Input } from "antd";
import { connect } from "react-redux";

const Placeholder = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    placeholderText,
    placeholderStrings,
  } = props;

  const { label } = placeholderStrings;

  const [placeholderValue, setPlaceholderValue] = useState(
    getPropValue({ styling, id, target, style: "placeholder", extra: "" }) || ""
  );

  useEffect(() => {
    setPlaceholderValue(
      getPropValue({ styling, id, target, style: "placeholder", extra: "" }) ||
        ""
    );
  }, [id]);

  return (
    <div className="placeholder">
      <label htmlFor="placeholder">{label}:</label>
      <Input
        placeholder={placeholderText}
        value={placeholderValue ? placeholderValue : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              placeholder: value,
            },
          });
          setPlaceholderValue(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  placeholderStrings:
    store.i18n.backend.styling_and_option_controls.show_options.placeholder,
});

export default connect(mapStateToProps)(Placeholder);
