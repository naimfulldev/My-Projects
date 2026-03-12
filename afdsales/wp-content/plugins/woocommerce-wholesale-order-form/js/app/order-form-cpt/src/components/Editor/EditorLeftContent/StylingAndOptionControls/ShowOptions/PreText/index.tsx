import { useEffect, useState } from "react";
import { Input } from "antd";
import { connect } from "react-redux";

const PreText = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    placeholderText,
    preTextStrings,
    cartSubtotalStrings,
  } = props;

  const { label } = preTextStrings;
  const { subtotal_pretext } = cartSubtotalStrings;

  const [preTextValue, setPreTextValue] = useState(
    getPropValue({ styling, id, target, style: "preText", extra: "" }) || ""
  );

  useEffect(() => {
    setPreTextValue(
      getPropValue({ styling, id, target, style: "preText", extra: "" }) ||
        subtotal_pretext
    );
  }, [id]);

  return (
    <div className="pre-text">
      <label htmlFor="pre-text">{label}</label>
      <Input
        placeholder={placeholderText}
        value={preTextValue ? preTextValue : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              preText: value,
            },
          });
          setPreTextValue(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  preTextStrings:
    store.i18n.backend.styling_and_option_controls.show_options.pre_text,
  cartSubtotalStrings: store.i18n.backend.header_footer_elements.cart_subtotal,
});

export default connect(mapStateToProps)(PreText);
