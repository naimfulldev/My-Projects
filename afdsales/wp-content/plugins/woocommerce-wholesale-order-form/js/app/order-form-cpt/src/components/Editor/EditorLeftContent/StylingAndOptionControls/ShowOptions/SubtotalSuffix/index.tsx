import { useEffect, useState } from "react";
import { Input } from "antd";
import { connect } from "react-redux";

const SubtotalSuffix = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    placeholderText,
    subtotalSuffixStrings,
  } = props;

  const [subtotalSuffixValue, setSubtotalSuffixValue] = useState(
    getPropValue({ styling, id, target, style: "subtotalSuffix", extra: "" }) ||
      ""
  );

  useEffect(() => {
    setSubtotalSuffixValue(
      getPropValue({
        styling,
        id,
        target,
        style: "subtotalSuffix",
        extra: "",
      }) || ""
    );
  }, [id]);

  return (
    <div className="subtotal-suffix">
      <label htmlFor="subtotal-suffix">{subtotalSuffixStrings?.label}:</label>
      <Input
        placeholder={placeholderText}
        value={subtotalSuffixValue ? subtotalSuffixValue : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              subtotalSuffix: value,
            },
          });
          setSubtotalSuffixValue(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  subtotalSuffixStrings:
    store.i18n.backend.styling_and_option_controls.show_options.subtotal_suffix,
});

export default connect(mapStateToProps)(SubtotalSuffix);
