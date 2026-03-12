import { Button, Result } from "antd";
import { connect } from "react-redux";

const NotFound = (props: any) => {
  const { editorRightContent } = props;
  const { not_found, go_back } = editorRightContent;
  return (
    <Result
      style={{ marginTop: "100px" }}
      status="404"
      title="404"
      subTitle={not_found}
      extra={
        <Button type="primary" onClick={() => window.history.back()}>
          {go_back}
        </Button>
      }
    />
  );
};
const mapStateToProps = (store: any, props: any) => ({
  editorRightContent: store.i18n.backend.editor_right_content,
});

export default connect(mapStateToProps)(NotFound);
