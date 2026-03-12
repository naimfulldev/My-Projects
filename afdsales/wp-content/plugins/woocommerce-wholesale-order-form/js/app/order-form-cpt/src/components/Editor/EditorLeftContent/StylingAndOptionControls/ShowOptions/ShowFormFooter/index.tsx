import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const ShowFormFooter = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    showFormFooterStrings,
  } = props;

  const { label, description } = showFormFooterStrings;

  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "showFormFooter", extra: "" })
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "showFormFooter",
        extra: "",
      })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  return (
    <div className="show-form-footer">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              showFormFooter: e.target.checked,
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
  showFormFooterStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .show_form_footer,
});

export default connect(mapStateToProps)(ShowFormFooter);
