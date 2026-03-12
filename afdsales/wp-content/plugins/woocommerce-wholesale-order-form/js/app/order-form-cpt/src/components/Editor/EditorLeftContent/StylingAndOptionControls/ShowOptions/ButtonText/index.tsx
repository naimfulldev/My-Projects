import { useEffect, useState } from "react";
import { Input } from "antd";

// Redux
import { connect } from "react-redux";

const ButtonText = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    buttonText,
    buttonTextStrings,
  } = props;

  const { search_products, label1, label2 } = buttonTextStrings;

  const [buttonTextValue, setButtonTextValue] = useState(
    getPropValue({
      styling,
      id,
      target,
      style: buttonText || "buttonText",
      extra: "",
    }) || ""
  );

  useEffect(() => {
    if (styling.item.itemId === "search-button") {
      setButtonTextValue(search_products);
    } else {
      setButtonTextValue(
        getPropValue({
          styling,
          id,
          target,
          style: buttonText || "buttonText",
          extra: "",
        }) || ""
      );
    }
  }, [id]);

  let label = styling.item.itemId === "search-button" ? label2 : label1;

  return (
    <div className="button-text">
      <label htmlFor="button-text">{label}:</label>
      <Input
        placeholder={label}
        value={buttonTextValue ? buttonTextValue : ""}
        onChange={(e) => {
          const { value } = e.target;
          const btnName = buttonText || "buttonText";
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              [btnName]: value,
            },
          });
          setButtonTextValue(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  buttonTextStrings:
    store.i18n.backend.styling_and_option_controls.show_options.button_text,
});

export default connect(mapStateToProps)(ButtonText);
