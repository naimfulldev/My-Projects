import { useEffect, useState } from "react";
import { Input } from "antd";

// Redux
import { connect } from "react-redux";

const ColumnHeadingText = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    headingText,
    columnHeadingStrings,
  } = props;

  const { label } = columnHeadingStrings;

  const [columnTitle, setColumnHeadingText] = useState(
    getPropValue({ styling, id, target, style: "columnHeading", extra: "" }) ||
      headingText
  );

  useEffect(() => {
    setColumnHeadingText(
      getPropValue({
        styling,
        id,
        target,
        style: "columnHeading",
        extra: "",
      }) || headingText
    );
  }, [id]);

  return (
    <div className="table-column-heading-text">
      <label htmlFor="table-column-heading-text">{label}:</label>
      <Input
        placeholder={headingText}
        value={columnTitle}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              columnHeading: value,
            },
          });
          setColumnHeadingText(value);
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  columnHeadingStrings:
    store.i18n.backend.styling_and_option_controls.show_options
      .column_heading_text,
});

export default connect(mapStateToProps)(ColumnHeadingText);
