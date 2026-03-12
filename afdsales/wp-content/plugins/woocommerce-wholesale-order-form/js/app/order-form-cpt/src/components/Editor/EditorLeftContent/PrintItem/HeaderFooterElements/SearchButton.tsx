import { Button } from "antd";
import { connect } from "react-redux";

const SearchButton = (props: any) => {
  const { properties, getPropValue, style, searchButtonStrings } = props;

  let buttonColor = getPropValue({
    properties,
    prop: "buttonColor",
  });

  let buttonTextColor = getPropValue({
    properties,
    prop: "buttonTextColor",
  });

  let showClearButton = getPropValue({
    properties,
    prop: "showClearButton",
  });

  showClearButton = showClearButton === null ? true : showClearButton;

  const searchButtonElementStyle = {
    background: buttonColor,
    color: buttonTextColor,
    border: buttonColor,
  };

  return (
    <div
      className="search-and-clear-buttons"
      style={{ whiteSpace: "pre-wrap" }}
    >
      <Button
        size="large"
        type="primary"
        style={{ ...style, ...searchButtonElementStyle }}
      >
        {getPropValue({ properties, prop: "searchButtonText" }) ||
          searchButtonStrings?.search_button_text}
      </Button>{" "}
      {showClearButton ? (
        <Button size="large" style={{ ...style, width: "auto" }}>
          {getPropValue({ properties, prop: "clearButtonText" }) ||
            searchButtonStrings?.clear_button_text}
        </Button>
      ) : (
        ""
      )}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  searchButtonStrings: store.i18n.backend.header_footer_elements.search_button,
});

export default connect(mapStateToProps)(SearchButton);
