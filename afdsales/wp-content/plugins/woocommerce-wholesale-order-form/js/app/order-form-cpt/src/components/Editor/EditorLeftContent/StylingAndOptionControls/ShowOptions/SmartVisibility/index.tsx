import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const SmartVisibility = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    smartVisibilityStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "smartVisibility",
      extra: "",
    }) || true
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "smartVisibility",
        extra: "",
      })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  return (
    <div className="smart-visibility">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              smartVisibility: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {smartVisibilityStrings?.label}
      </Checkbox>
      <em>{smartVisibilityStrings?.description}</em>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  smartVisibilityStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .smart_visibility,
});

export default connect(mapStateToProps)(SmartVisibility);
