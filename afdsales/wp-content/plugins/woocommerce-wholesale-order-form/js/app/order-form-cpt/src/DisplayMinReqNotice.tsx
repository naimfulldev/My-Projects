import { useState, useEffect } from "react";
import { Alert } from "antd";

import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

// Actions
import { minRequirements } from "./store/actions";
const { wwppRequiredVersions, removeMinimumWWPPFailMessage } = minRequirements;

const DisplayMinReqNotice = (props: any) => {
  const { actions } = props;

  const [minimumWWPPFailSubject, setMinimumWWPPFailSubject] = useState("");
  const [minimumWWPPFailMessage, setMinimumWWPPFailMessage] = useState("");

  useEffect(() => {
    try {
      actions.wwppRequiredVersions({
        successCB: (data: any) => {},
        failCB: (data: any) => {
          setMinimumWWPPFailSubject(data.heading);
          setMinimumWWPPFailMessage(data.message);
        },
      });
    } catch (e) {
      console.log(e);
    }
  }, []);

  const onClose = () => {
    actions.removeMinimumWWPPFailMessage({
      successCB: (data: any) => {
        setMinimumWWPPFailSubject("");
        setMinimumWWPPFailMessage("");
      },
      failCB: (data: any) => {},
    });
  };

  if (minimumWWPPFailSubject && minimumWWPPFailMessage)
    return (
      <Alert
        style={{
          padding: "20px 30px",
          marginBottom: "20px",
          background: "#fff",
          border: "1px solid #eee",
          borderLeft: "4px solid #46bf92",
          borderRadius: "0px",
        }}
        message={
          <div
            dangerouslySetInnerHTML={{ __html: minimumWWPPFailSubject }}
          ></div>
        }
        description={
          <div
            dangerouslySetInnerHTML={{ __html: minimumWWPPFailMessage }}
          ></div>
        }
        type="error"
        closable
        onClose={onClose}
      />
    );
  else return <></>;
};

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      wwppRequiredVersions,
      removeMinimumWWPPFailMessage,
    },
    dispatch
  ),
});

export default connect(null, mapDispatchToProps)(DisplayMinReqNotice);
