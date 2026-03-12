import { useEffect, useState } from "react";
import { Input } from "antd";
import { connect } from "react-redux";

const EmptyCartText = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    placeholderText,
    emptyCartStrings,
  } = props;

  const { label, empty_cart_text } = emptyCartStrings;

  const [emptyCartValue, setEmptyCartTextValue] = useState(
    getPropValue({ styling, id, target, style: "emptyCartText", extra: "" }) ||
      ""
  );

  useEffect(() => {
    setEmptyCartTextValue(
      getPropValue({
        styling,
        id,
        target,
        style: "emptyCartText",
        extra: "",
      }) || empty_cart_text
    );
  }, [id]);

  return (
    <div className="empty-cart-text">
      <label htmlFor="empty-cart-text">{label}:</label>
      <Input
        placeholder={placeholderText}
        value={emptyCartValue ? emptyCartValue : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              emptyCartText: value,
            },
          });
          setEmptyCartTextValue(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  emptyCartStrings:
    store.i18n.backend.styling_and_option_controls.show_options.empty_cart_text,
});

export default connect(mapStateToProps)(EmptyCartText);
