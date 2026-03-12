import { useEffect, useState } from "react";
import { Button } from "antd";

// Redux
import { connect } from "react-redux";

const AddSelectedToCart = (props: any) => {
  const { properties, getPropValue, style, addSelectedToCartStrings } = props;

  const { button_text } = addSelectedToCartStrings;

  const [buttonName, setButtonName] = useState(
    getPropValue({ properties, prop: "buttonText" }) ?? button_text
  );
  let buttonColor = getPropValue({
    properties,
    prop: "buttonColor",
  });

  let buttonTextColor = getPropValue({
    properties,
    prop: "buttonTextColor",
  });

  const addSelectedToCartElementStyle = {
    background: buttonColor,
    color: buttonTextColor,
    border: buttonColor,
  };

  useEffect(() => {
    const text = getPropValue({ properties, prop: "buttonText" });

    if (text !== null && text !== "") setButtonName(text);
    else setButtonName(button_text);
  }, [properties]);

  return (
    <>
      <Button
        size="large"
        type="primary"
        style={{ ...style, ...addSelectedToCartElementStyle }}
      >
        {buttonName}
      </Button>
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  addSelectedToCartStrings:
    store.i18n.backend.header_footer_elements.add_selected_to_cart,
});

export default connect(mapStateToProps)(AddSelectedToCart);
