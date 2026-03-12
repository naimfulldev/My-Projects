import { useState } from "react";
import { Popover, Button } from "antd";
import { PlusCircleOutlined } from "@ant-design/icons";
import generateId from "helpers/generateId";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { dragAndDropActions } from "store/actions";
const { setDndData } = dragAndDropActions;

const AddNewSection = (props: any) => {
  const { data, sectionId, hasRows, actions, sectionHeaderFooterStrings } =
    props;
  const [visible, setVisible] = useState(false);

  const {
    one_column,
    two_column,
    three_column,
    four_column,
    new_section,
    select_structure,
  } = sectionHeaderFooterStrings;

  const setNewSection = (cols: any) => {
    const newCols = Array.from(Array(cols)).map((x, i) => {
      return {
        colId: generateId(), //Generate random id for col
        itemIds: [],
      };
    });

    const newData = [
      {
        rowId: generateId(), //Generate random id for row
        columns: newCols,
      },
    ];

    const newRow = [...data["editorArea"][sectionId]["rows"], ...newData];

    actions.setDndData({
      ...data,
      editorArea: {
        ...data["editorArea"],
        [sectionId]: {
          ...data["editorArea"][sectionId],
          rows: newRow,
        },
      },
    });
  };

  const SectionSetup = (
    <div className="Setup">
      <h4>{one_column}</h4>
      <div
        className="Columns OneColumn"
        onClick={() => {
          setVisible(false);
          setNewSection(1);
        }}
      >
        <div />
      </div>
      <h4>{two_column}</h4>
      <div
        className="Columns TwoColumn"
        onClick={() => {
          setVisible(false);
          setNewSection(2);
        }}
      >
        <div />
        <div />
      </div>
      <h4>{three_column}</h4>
      <div
        className="Columns ThreeColumn"
        onClick={() => {
          setVisible(false);
          setNewSection(3);
        }}
      >
        <div />
        <div />
        <div />
      </div>
      <h4>{four_column}</h4>
      <div
        className="Columns ThreeColumn"
        onClick={() => {
          setVisible(false);
          setNewSection(4);
        }}
      >
        <div />
        <div />
        <div />
        <div />
      </div>
    </div>
  );

  return (
    <>
      {hasRows ? (
        <div
          style={{
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
          }}
        >
          <Popover
            trigger="click"
            content={SectionSetup}
            title={select_structure}
            visible={visible}
            onVisibleChange={(visible) => setVisible(visible)}
          >
            <Button className="add-new-section">
              <PlusCircleOutlined />
              {new_section}
            </Button>
          </Popover>
        </div>
      ) : (
        <div
          style={{
            // border: "1px solid #eee",
            background: "#FAFAFA",
            padding: 10,
            minHeight: "150px",
            display: "flex",
            justifyContent: "center",
            alignItems: "center",
          }}
        >
          <Popover
            trigger="click"
            content={SectionSetup}
            title={select_structure}
            visible={visible}
            onVisibleChange={(visible) => setVisible(visible)}
          >
            <Button>
              <PlusCircleOutlined />
              {new_section}
            </Button>
          </Popover>
        </div>
      )}
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  data: store.dragAndDrop,
  sectionHeaderFooterStrings: store.i18n.backend.section_header_footer,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setDndData,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(AddNewSection);
