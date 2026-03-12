export const readableStyles = (props: any) => {
  const { styles } = props;

  let stylesCopy: any = {};
  if (typeof styles === "undefined") return;

  Object.keys(styles).map((style: any, index: any) => {
    switch (style) {
      case "width":
        if (styles[style].type === "percentage")
          stylesCopy[style] = `${styles[style].value}%`;
        else if (styles[style].type === "pixels")
          stylesCopy[style] = `${styles[style].value}px`;
        break;
      case "fontSize":
        if (styles[style].type === "percentage")
          stylesCopy[style] = `${styles[style].value}%`;
        else if (styles[style].type === "pixels")
          stylesCopy[style] = `${styles[style].value}px`;
        break;
      default:
        stylesCopy[style] = styles[style];
    }
    return;
  });

  return stylesCopy;
};
