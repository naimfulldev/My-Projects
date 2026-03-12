export const getPropValue = (props: any) => {
  const { formStyles, item, prop } = props;

  if (
    typeof formStyles !== "undefined" &&
    typeof formStyles[item] !== "undefined" &&
    typeof formStyles[item]["props"] !== "undefined" &&
    typeof formStyles[item]["props"][prop] !== "undefined"
  )
    return formStyles[item]["props"][prop];
  else return null;
};
