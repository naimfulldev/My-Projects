import { useState } from "react";
import { Tooltip, InputNumber } from "antd";
import { LinkOutlined, DisconnectOutlined } from "@ant-design/icons";
import { connect } from "react-redux";

const Padding = (props: any) => {
  const { styling, setStyles, id, target, updateStyling, paddingStrings } =
    props;
  const [linkPadding, setLinkPadding] = useState(true);

  const paddings: any = {
    Top: "paddingTop",
    Right: "paddingRight",
    Bottom: "paddingBottom",
    Left: "paddingLeft",
  };

  const inputNumbers = Object.keys(paddings).map((key: any) => {
    const paddingSection = styling?.styles?.[id]?.[target]?.[paddings?.[key]];
    const padding =
      typeof paddingSection !== "undefined" && paddingSection > 0
        ? paddingSection
        : 0;

    return (
      <InputNumber
        placeholder={key}
        key={key}
        onChange={(val: any) =>
          linkPadding
            ? updateStyling({
                setStyles,
                styling,
                id,
                target,
                toUpdate: {
                  paddingTop: val,
                  paddingRight: val,
                  paddingBottom: val,
                  paddingLeft: val,
                },
              })
            : updateStyling({
                setStyles,
                styling,
                id,
                target,
                toUpdate: {
                  [paddings[key]]: val,
                },
              })
        }
        value={padding}
      />
    );
  });

  return (
    <div className="padding">
      <label htmlFor="padding">{paddingStrings?.label}:</label>
      {inputNumbers}
      {linkPadding ? (
        <Tooltip title={paddingStrings?.linked}>
          <LinkOutlined
            onClick={() => {
              setLinkPadding(false);
            }}
            style={{ color: "#0071a1" }}
          />
        </Tooltip>
      ) : (
        <Tooltip title={paddingStrings?.not_linked}>
          <DisconnectOutlined
            onClick={() => {
              setLinkPadding(true);
              updateStyling({
                setStyles,
                styling,
                id,
                target,
                toUpdate: {
                  paddingTop: styling.styles[id][target]["paddingTop"],
                  paddingRight: styling.styles[id][target]["paddingTop"],
                  paddingBottom: styling.styles[id][target]["paddingTop"],
                  paddingLeft: styling.styles[id][target]["paddingTop"],
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
  paddingStrings:
    store.i18n.backend.styling_and_option_controls.show_styles.padding,
});

export default connect(mapStateToProps)(Padding);
