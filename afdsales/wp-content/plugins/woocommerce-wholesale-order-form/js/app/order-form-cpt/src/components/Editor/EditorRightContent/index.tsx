import { Tabs, Form, Affix } from "antd";
import {
  TableOutlined,
  SettingOutlined,
  ReadOutlined,
} from "@ant-design/icons";
import EditFormTab from "./EditFormTab";
import FormSettingsTab from "./FormSettingsTab";
import LocationsTab from "./LocationsTab";

import { defaults } from "store/reducers/dragAndDropReducer";

// Redux
import { connect } from "react-redux";

const { TabPane } = Tabs;

// Update saved data
const updateData = (savedData: any, defaultData: any) => {
  savedData.items = defaultData.items;
  return savedData;
};

const EditorRightContent = (props: any) => {
  const { data, editorRightContentStrings } = props;
  const { edit_form, settings, locations } = editorRightContentStrings;
  const updatedData = updateData(data, defaults);

  return (
    <div className="right-content" style={{ position: "relative" }}>
      <Affix offsetTop={100} style={{ position: "absolute", left: "0px" }}>
        <Tabs type="card">
          <TabPane
            tab={
              <>
                <TableOutlined />
                {edit_form}
              </>
            }
            key="1"
          >
            <EditFormTab data={updatedData} />
          </TabPane>
          <TabPane
            tab={
              <>
                <SettingOutlined />
                {settings}
              </>
            }
            key="2"
          >
            <Form className="form-settings">
              <FormSettingsTab />
            </Form>
          </TabPane>
          <TabPane
            tab={
              <>
                <ReadOutlined />
                {locations}
              </>
            }
            key="3"
          >
            <LocationsTab />
          </TabPane>
        </Tabs>
      </Affix>
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  data: store.dragAndDrop,
  editorRightContentStrings: store.i18n.backend.editor_right_content,
});
export default connect(mapStateToProps)(EditorRightContent);
