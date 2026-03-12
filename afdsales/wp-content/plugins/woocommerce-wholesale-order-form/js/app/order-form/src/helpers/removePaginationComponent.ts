/*
 * Removes Pagination Component from displaying in header/footer in the frontend
 * @since 1.19
 * @return object
 */
interface IData {
  title: string;
  rows: any[];
}

export const removePaginationComponent = (props: any) => {
  const { formSettings, formHeader, formFooter } = props;

  if (
    typeof formSettings.lazy_loading !== "undefined" &&
    formSettings.lazy_loading === true
  ) {
    let updatedComponents = {};
    let removePagination = (data: IData) => {
      Object.keys(data["rows"]).map((d: any, i: number) => {
        Object.keys(data["rows"][i]["columns"]).map((row: any, j: number) => {
          if (data["rows"][i]["columns"][j].itemIds.includes("pagination")) {
            let filtered = data["rows"][i]["columns"][j].itemIds.filter(
              (el: string) => {
                return el != "pagination";
              }
            );

            data["rows"][i]["columns"][j].itemIds = filtered;
          }
        });
      });
      return data;
    };

    if (typeof formHeader !== "undefined") {
      updatedComponents = removePagination(formHeader);
    } else {
      updatedComponents = removePagination(formFooter);
    }
    return updatedComponents;
  }

  return typeof formHeader !== "undefined" ? formHeader : formFooter;
};
