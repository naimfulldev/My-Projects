import { useEffect, useState } from "react";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { updateStyling } from "helpers/updateStyling";
import { Input } from "antd";

// Actions
import { stylingActions } from "store/actions";

const { setStyles } = stylingActions;

const SearchInput = (props: any) => {
  const {
    styling,
    actions,
    style,
    properties,
    getPropValue,
    searchInputStrings,
  } = props;
  const { setStyles } = actions;

  const { search_button_text } = searchInputStrings;

  const [placeholder, setPlaceholder] = useState(
    getPropValue({ properties, prop: "placeholder" }) ?? search_button_text
  );

  useEffect(() => {
    const text = getPropValue({ properties, prop: "placeholder" });

    if (text !== null && text !== "") setPlaceholder(text);
    else setPlaceholder(search_button_text);
  }, [properties]);

  useEffect(() => {
    try {
      const check = getPropValue({ properties, prop: "submitOnEnter" });

      if (check === null) {
        updateStyling({
          setStyles,
          styling,
          id: "search-input",
          target: "props",
          toUpdate: {
            submitOnEnter: true,
          },
        });
      }
    } catch (e) {
      console.log(e);
    }
  }, []);

  return (
    <>
      <Input
        size="large"
        placeholder={placeholder}
        style={{
          borderRadius: "0px",
          borderColor: "#d9d9d9",
          padding: "3px 8px",
          width: "250px",
          ...style,
        }}
      />
    </>
  );
};

const mapStateToProps = (store: any) => ({
  styling: store.styling,
  searchInputStrings: store.i18n.backend.header_footer_elements.search_input,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setStyles,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(SearchInput);
