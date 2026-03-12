import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const SubmitOnEnter = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    submitOnEnterStrings,
  } = props;
  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "submitOnEnter", extra: "" })
  );

  useEffect(() => {
    setValue(
      getPropValue({ styling, id, target, style: "submitOnEnter", extra: "" })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  return (
    <div className="submit-on-enter">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              submitOnEnter: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {submitOnEnterStrings?.label}
      </Checkbox>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  submitOnEnterStrings:
    store.i18n.backend.styling_and_option_controls.show_options.submit_on_enter,
});

export default connect(mapStateToProps)(SubmitOnEnter);
