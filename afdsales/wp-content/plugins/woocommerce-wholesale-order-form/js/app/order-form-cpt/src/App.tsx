import { useEffect } from "react";
import OrderFormsTable from "./components/OrderFormsTable";
import Editor from "./components/Editor";
import DisplayMinReqNotice from "./DisplayMinReqNotice";

import "antd/dist/antd.css";
import { Button, PageHeader } from "antd";
import { useLocation, Link } from "react-router-dom";

// Redux
import { i18nActions } from "store/actions";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

const { fetchStrings } = i18nActions;

const App = (props: any) => {
  const { backendStrings, actions } = props;
  const pathName = useLocation().pathname;
  const params = useLocation().search;
  const urlParams = new URLSearchParams(params);

  const page = urlParams.get("sub-page");
  const postID = urlParams.get("post") || 0;

  useEffect(() => {
    actions.fetchStrings();
  }, []);

  if (backendStrings.length <= 0) {
    return <div>loading</div>;
  }

  const { add_form, heading, description } = backendStrings.app;

  const RenderComponent = () => {
    // Edit Order Form Page
    if (page === "add-new" || (page === "edit" && postID > 0))
      return <Editor />;
    else {
      // Order Forms Table
      const siteURL =
        params.length > 0 ? `${pathName}${params}&` : `${pathName}?`;
      return (
        <>
          <DisplayMinReqNotice />
          <PageHeader
            className="page-header"
            title={`${heading}`}
            subTitle={
              <Link to={`${siteURL}sub-page=add-new`}>
                <Button
                  style={{
                    background: "#f3f5f6",
                    color: "#0071a1",
                    border: "1px solid #0071a1",
                    fontWeight: "bold",
                  }}
                >
                  {add_form}
                </Button>
              </Link>
            }
          />

          <p
            style={{ margin: "20px 0px", fontSize: "16px" }}
          >{`${description}`}</p>
          <OrderFormsTable />
        </>
      );
    }
  };

  return (
    <div className={`order-form-cpt ${page ? page : "home"}`}>
      <RenderComponent />
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  backendStrings: store.i18n.backend,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      fetchStrings,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(App);
