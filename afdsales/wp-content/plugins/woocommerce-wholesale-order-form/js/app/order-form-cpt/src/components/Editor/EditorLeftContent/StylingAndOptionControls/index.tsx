import { useState, useEffect } from "react";
import { Drawer, Collapse } from "antd";
import ShowStyles from "./ShowStyles";
import ShowOptions from "./ShowOptions";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

// Actions
import { stylingActions } from "store/actions";
import { defaults } from "store/reducers/dragAndDropReducer";

const { setShowStyling, setStyles } = stylingActions;

const { Panel } = Collapse;

const DisplayDrawerPanels = (props: any) => {
  const { styleProps, styling, styles_text, options_text } = props;
  const [showOption, setShowOption] = useState(true);

  useEffect(() => {
    try {
      if (
        styling.item.type === "ROW" ||
        (styling.item.type === "ITEM" &&
          styling.item.itemId === "product-count") ||
        styling.item.id === "formTable"
      ) {
        setShowOption(false);
      } else {
        setShowOption(true);
      }
    } catch (error) {
      console.log(error);
    }
  }, [styling]);

  return (
    <Collapse defaultActiveKey={["1"]}>
      <Panel header={styles_text} key="1">
        <ShowStyles {...styleProps} />
      </Panel>
      <Panel
        header={options_text}
        key="2"
        style={{
          display: showOption ? "block" : "none",
        }}
      >
        <ShowOptions {...styleProps} />
      </Panel>
    </Collapse>
  );
};

const StylingAndOptionControls = (props: any) => {
  const { styling, actions, stylingAndOptionControlsStrings } = props;
  const { setShowStyling, setStyles } = actions;
  const [itemName, setItemName] = useState("");

  const styleProps = { styling, setStyles };
  const defaultItems: any = defaults;

  const { setting_for, setting_for_this_row, styles, options } =
    stylingAndOptionControlsStrings;

  useEffect(() => {
    if (
      styling.show === true &&
      typeof styling.item.itemId !== "undefined" &&
      styling.item.itemId !== "none"
    ) {
      setItemName(defaultItems.items[styling.item.itemId].content);
    } else {
      setItemName("");
    }
  }, [styling.item]);

  const sectionTitle = (section: string) => {
    switch (section) {
      case "formHeader":
        return "Header";
      case "formTable":
        return "Table";
      case "formFooter":
        return "Footer";
      default:
        return "";
    }
  };

  return (
    <Drawer
      title={`${
        styling.item.type === "SECTION"
          ? `${setting_for} ${sectionTitle(styling.item.section)}`
          : styling.item.type === "ROW"
          ? setting_for_this_row
          : `${setting_for} ${itemName}`
      }`}
      placement="right"
      closable={true}
      onClose={() => setShowStyling({ show: false })}
      visible={styling.show}
      mask={false}
      width={400}
    >
      <div className="stylings">
        <DisplayDrawerPanels
          styleProps={styleProps}
          styling={styling}
          styles_text={styles}
          options_text={options}
        />
      </div>
    </Drawer>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  styling: store.styling,
  stylingAndOptionControlsStrings:
    store.i18n.backend.styling_and_option_controls,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setShowStyling,
      setStyles,
    },
    dispatch
  ),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(StylingAndOptionControls);
