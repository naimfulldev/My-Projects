import { useEffect, useState } from "react";
import { InputNumber } from "antd";
import { connect } from "react-redux";

const ImageSize = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    imageSizeStrings,
  } = props;

  const { label, width_text, height_text } = imageSizeStrings;

  const [imageSizeValue, setImageSizeValue] = useState(
    getPropValue({ styling, id, target, style: "imageSize", extra: "" }) || ""
  );

  useEffect(() => {
    setImageSizeValue(
      getPropValue({ styling, id, target, style: "imageSize", extra: "" }) || {
        width: 48,
        height: 48,
      }
    );
  }, [id]);

  return (
    <div className="image-size">
      <label htmlFor="image-size">{label}:</label>
      {width_text}:&nbsp;
      <InputNumber
        style={{ width: "70px" }}
        value={imageSizeValue.width ? imageSizeValue.width : ""}
        onChange={(value) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              imageSize: {
                width: value,
                height: imageSizeValue.height,
              },
            },
          });
          setImageSizeValue({
            width: value,
            height: imageSizeValue.height,
          });
        }}
      />
      {height_text}:&nbsp;
      <InputNumber
        style={{ width: "70px" }}
        value={imageSizeValue.height ? imageSizeValue.height : ""}
        onChange={(value) => {
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              imageSize: {
                width: imageSizeValue.width,
                height: value,
              },
            },
          });
          setImageSizeValue({
            width: imageSizeValue.width,
            height: value,
          });
        }}
      />
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  imageSizeStrings:
    store.i18n.backend.styling_and_option_controls.show_options.image_size,
});

export default connect(mapStateToProps)(ImageSize);
