import { useEffect, useState } from "react";
import { Checkbox } from "antd";
import { connect } from "react-redux";

const SkuSearch = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    skuSearchStrings,
  } = props;

  const [value, setValue] = useState(
    getPropValue({ styling, id, target, style: "skuSearch", extra: "" })
  );

  useEffect(() => {
    setValue(
      getPropValue({ styling, id, target, style: "skuSearch", extra: "" })
    );
  }, [id]);

  if (value === undefined || value === null) {
    setValue(true);
  }

  return (
    <div className="sku-search">
      <Checkbox
        checked={value}
        onChange={(e: any) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              skuSearch: e.target.checked,
            },
          });
          setValue(e.target.checked);
        }}
      >
        {skuSearchStrings?.label}
      </Checkbox>
      <em>{skuSearchStrings?.description}</em>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  skuSearchStrings:
    store.i18n.backend.styling_and_option_controls.show_options.sku_search,
});

export default connect(mapStateToProps)(SkuSearch);
