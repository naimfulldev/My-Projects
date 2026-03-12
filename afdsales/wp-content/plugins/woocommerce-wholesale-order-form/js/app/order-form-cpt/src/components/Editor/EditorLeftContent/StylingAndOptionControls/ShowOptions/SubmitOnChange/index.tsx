import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const SubmitOnChange = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    submitOnChangeStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "submitOnChange", extra: "" })
  );

  useEffect(() => {
    setValue(
      getPropValue({ styling, id, target, style: "submitOnChange", extra: "" })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  return (
    <div className="submit-on-change">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              submitOnChange: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {submitOnChangeStrings?.label}
      </Checkbox>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  submitOnChangeStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .submit_on_change,
});

export default connect(mapStateToProps)(SubmitOnChange);
