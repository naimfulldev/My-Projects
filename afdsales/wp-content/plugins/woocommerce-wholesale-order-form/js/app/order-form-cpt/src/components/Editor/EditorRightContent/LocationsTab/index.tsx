import { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import { List } from "antd";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

import { orderFormActions } from "store/actions";

const { fetchOrderForm } = orderFormActions;

const Locations = (props: any) => {
  const { actions, formSettingsTabStrings } = props;
  const { fetchOrderForm } = actions;
  const { locations_tab_heading, locations_tab_desc, no_data } =
    formSettingsTabStrings;

  const [locations, setLocations] = useState([]);
  const params = useLocation().search;
  const urlParams = new URLSearchParams(params);
  const postID = urlParams.get("post") || 0;

  useEffect(() => {
    if (postID > 0) {
      fetchOrderForm({
        id: postID,
        successCB: (data: any) => {
          setLocations(data.locations);
        },
        failCB: () => {
          console.log("error");
        },
      });
    }
  }, [fetchOrderForm, postID]);

  return (
    <>
      <h3 style={{ fontWeight: "bolder" }}>{locations_tab_heading}</h3>
      <p>{locations_tab_desc}</p>
      <List
        locale={{ emptyText: no_data ?? "No Data" }}
        dataSource={locations}
        renderItem={(item: any) => {
          return (
            <List.Item>
              <a href={item.permalink} target="_blank" rel="noreferrer">
                {item.post_title}
              </a>
            </List.Item>
          );
        }}
      />
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  formSettingsTabStrings: store.i18n.backend.locations_tab,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      fetchOrderForm,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(Locations);
