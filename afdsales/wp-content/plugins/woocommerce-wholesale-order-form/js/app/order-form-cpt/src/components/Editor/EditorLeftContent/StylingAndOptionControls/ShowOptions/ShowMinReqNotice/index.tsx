import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

declare var Options: any;

const ShowMinReqNotice = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    showMinReqNoticeStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: "showMinReqNotice",
      extra: "",
    })
  );

  useEffect(() => {
    setValue(
      getPropValue({
        styling,
        id,
        target,
        style: "showMinReqNotice",
        extra: "",
      })
    );
  }, [id]);

  if (value === null || value === undefined) {
    setValue(true);
  }

  if (Options.wwpp_active === "") {
    return <></>;
  } else {
    return (
      <div className="show-min-req-notice">
        <Checkbox
          checked={value}
          onChange={(e: any) => {
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: {
                showMinReqNotice: e.target.checked,
              },
            });
            setValue(e.target.checked);
          }}
        >
          {showMinReqNoticeStrings?.label}
        </Checkbox>
        <em>{showMinReqNoticeStrings?.description}</em>
      </div>
    );
  }
};

const mapStateToProps = (store: any) => ({
  showMinReqNoticeStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .show_min_req_notice,
});

export default connect(mapStateToProps)(ShowMinReqNotice);
