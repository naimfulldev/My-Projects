import { useEffect, useState } from "react";
import { Input } from "antd";
import { connect } from "react-redux";

const MaxCharacters = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    placeholderText,
    maxCharactersStrings,
  } = props;

  const { label, description } = maxCharactersStrings;

  const [preTextValue, setMaxCharacters] = useState(
    getPropValue({ styling, id, target, style: "maxCharacters", extra: "" })
  );

  useEffect(() => {
    setMaxCharacters(
      getPropValue({
        styling,
        id,
        target,
        style: "maxCharacters",
        extra: "",
      })
    );
  }, [id]);

  if (preTextValue === undefined || preTextValue === null) {
    setMaxCharacters(200);
  }

  return (
    <div className="max-characters">
      <label htmlFor="max-characters">{label}:</label>
      <Input
        placeholder={placeholderText}
        value={preTextValue ? preTextValue : ""}
        onChange={(e) => {
          const { value } = e.target;
          updateStyling({
            setStyles,
            styling,
            id,
            target,
            toUpdate: {
              maxCharacters: value,
            },
          });
          setMaxCharacters(value);
        }}
      />
      <em>{description}</em>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  maxCharactersStrings:
    store.i18n.backend.styling_and_option_controls.show_options.max_characters,
});

export default connect(mapStateToProps)(MaxCharacters);
