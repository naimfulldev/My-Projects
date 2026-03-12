export const getRowsHeight = (props: any) => {
  const { i } = props;

  let heightCSS: any = [];
  let rowsHeight: any = localStorage.getItem("rowsHeight") || [];

  if (typeof rowsHeight === "string") {
    rowsHeight = JSON.parse(rowsHeight);
  }
  if (typeof rowsHeight[i] !== "undefined") {
    heightCSS = {
      height: rowsHeight[i] + "px",
    };
  }
  return heightCSS;
};
