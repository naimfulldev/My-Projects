export const getPropValue = (props: any) => {
  const { styling, id, target, style, extra } = props;

  if (extra) {
    if (
      typeof styling?.styles?.[id]?.[target]?.[style]?.[extra] !== "undefined"
    )
      return styling.styles[id][target][style][extra];
    else return null;
  } else {
    if (typeof styling?.styles?.[id]?.[target]?.[style] !== "undefined")
      return styling.styles[id][target][style];
    else return null;
  }
};
