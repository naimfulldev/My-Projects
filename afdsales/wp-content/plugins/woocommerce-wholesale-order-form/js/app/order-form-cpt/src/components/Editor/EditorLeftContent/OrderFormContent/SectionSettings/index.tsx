import { FormOutlined } from "@ant-design/icons";
import { Tooltip } from "antd";
import "./style.scss";
import { connect } from "react-redux";

const SectionSettings = (props: any) => {
  const { sectionId, hasItems, setShowStyling, sectionSettingsStrings } = props;
  let section = "";

  switch (sectionId) {
    case "formHeader":
      section = "Header";
      break;
    case "formTable":
      section = "Table";
      break;
    case "formFooter":
      section = "Footer";
      break;
    default:
      break;
  }

  if (hasItems)
    return (
      <div className={`section-settings setting-${sectionId}`}>
        <Tooltip
          title={`${sectionSettingsStrings?.["settings_for"]} ${
            sectionSettingsStrings?.[sectionId] ?? section
          }.`}
        >
          <FormOutlined
            onClick={() =>
              setShowStyling({
                show: true,
                type: "SECTION",
                id: sectionId,
                itemId: "none",
                section: sectionId,
              })
            }
          />
        </Tooltip>
      </div>
    );
  else return <></>;
};

const mapStateToProps = (store: any) => ({
  sectionSettingsStrings: store.i18n.backend.section_settings,
});

export default connect(mapStateToProps)(SectionSettings);
