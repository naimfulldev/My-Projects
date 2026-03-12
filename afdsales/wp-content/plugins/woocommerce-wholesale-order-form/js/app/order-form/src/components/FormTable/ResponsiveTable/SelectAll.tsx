import { Checkbox } from "antd";
import { connect } from "react-redux";

const SelectAll = (props: any) => {
  const {
    selected,
    setSelectedAll,
    selectedAll,
    setSomethingChanged,
    formTable,
    responsiveTableStrings,
  } = props;

  return formTable?.itemIds?.length > 0 &&
    formTable.itemIds.includes("add-to-cart-checkbox") ? (
    <div className="select-all" style={{ display: "none" }}>
      <Checkbox
        style={{ fontSize: "18px" }}
        checked={selected}
        onChange={() => {
          setSelectedAll(!selectedAll);
          setSomethingChanged(true);
        }}
      >
        {responsiveTableStrings?.select_all}
      </Checkbox>
    </div>
  ) : (
    <></>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  responsiveTableStrings: store.i18n.frontend.responsive_table,
});

export default connect(mapStateToProps)(SelectAll);
