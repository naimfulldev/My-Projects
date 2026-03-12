import { useState } from "react";
import { Tooltip, InputNumber } from "antd";
import { LinkOutlined, DisconnectOutlined } from "@ant-design/icons";
import { connect } from "react-redux";

const Margin = (props: any) => {
  const { styling, setStyles, id, target, updateStyling, marginStrings } =
    props;
  const [linkMargin, setLinkMargin] = useState(true);

  const margins: any = {
    Top: "marginTop",
    Right: "marginRight",
    Bottom: "marginBottom",
    Left: "marginLeft",
  };

  const inputNumbers = Object.keys(margins).map((key: any) => {
    const marginSection = styling?.styles?.[id]?.[target]?.[margins[key]];
    const margin =
      typeof marginSection !== "undefined" && marginSection > 0
        ? marginSection
        : 0;

    return (
      <InputNumber
        placeholder={key}
        key={key}
        onChange={(val: any) => {
          if (linkMargin)
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: {
                marginTop: val,
                marginRight: val,
                marginBottom: val,
                marginLeft: val,
              },
            });
          else
            updateStyling({
              setStyles,
              styling,
              id,
              target,
              toUpdate: {
                [margins[key]]: val,
              },
            });
        }}
        value={margin}
      />
    );
  });

  return (
    <div className="margin">
      <label htmlFor="margin">{marginStrings?.label}:</label>
      {inputNumbers}
      {linkMargin ? (
        <Tooltip title={marginStrings?.linked}>
          <LinkOutlined
            onClick={() => {
              setLinkMargin(false);
            }}
            style={{ color: "#0071a1" }}
          />
        </Tooltip>
      ) : (
        <Tooltip title={marginStrings?.not_linked}>
          <DisconnectOutlined
            onClick={() => {
              const marginTop = styling?.styles?.[id]?.[target]?.["marginTop"];
              const margin =
                typeof marginTop !== "undefined" && marginTop > 0
                  ? marginTop
                  : 0;

              setLinkMargin(true);
              updateStyling({
                setStyles,
                styling,
                id,
                target,
                toUpdate: {
                  marginTop: margin,
                  marginRight: margin,
                  marginBottom: margin,
                  marginLeft: margin,
                },
              });
            }}
          />
        </Tooltip>
      )}
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  marginStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.margin,
});

export default connect(mapStateToProps)(Margin);
