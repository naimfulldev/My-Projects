import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const ShowFormHeader = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    showFormHeaderStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "showFormHeader", extra: "" })
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "showFormHeader",
        extra: "",
      })
    );
  }, [id]);

  if (value === null || value === undefined) {
    setValue(true);
  }

  return (
    <div className="show-form-header">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              showFormHeader: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {showFormHeaderStrings?.label}
      </Checkbox>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  showFormHeaderStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .show_form_header,
});

export default connect(mapStateToProps)(ShowFormHeader);
