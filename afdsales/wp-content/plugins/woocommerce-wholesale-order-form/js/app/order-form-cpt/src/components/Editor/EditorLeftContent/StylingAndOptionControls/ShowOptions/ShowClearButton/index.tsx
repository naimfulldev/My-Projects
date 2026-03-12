import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const ShowClearButton = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    showClearButtonStrings,
  } = props;

  const { label, description } = showClearButtonStrings;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "showClearButton",
      extra: "",
    })
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "showClearButton",
        extra: "",
      })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  return (
    <div className="sort-option">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              showClearButton: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {label}
      </Checkbox>
      <em>{description}</em>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  showClearButtonStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .show_clear_button,
});

export default connect(mapStateToProps)(ShowClearButton);
