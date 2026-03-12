import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const Sortable = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    sortableStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "sortable", extra: "" })
  );

  useEffect(() => {
    setValue(
      getPropValue({ styling, id, target, style: "sortable", extra: "" })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  return (
    <div className="sort-option">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              sortable: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {sortableStrings?.label}
      </Checkbox>
      <em>{sortableStrings?.description}</em>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  sortableStrings:
    store.i18n.backend.styling_and_option_controls.show_options.sortable,
});

export default connect(mapStateToProps)(Sortable);
