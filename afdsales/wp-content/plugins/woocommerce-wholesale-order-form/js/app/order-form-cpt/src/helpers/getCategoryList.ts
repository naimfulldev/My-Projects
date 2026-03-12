export const getCategoryList = (props: any) => {
  try {
    const { productCategories } = props;

    let treeData: any[] = [];

    let htmlDecode = (input: string) => {
      var doc = new DOMParser().parseFromString(input, "text/html");
      return doc.documentElement.textContent;
    };

    let iterate = (cat: any, data: any) => {
      cat.children.forEach((cat2: any, index: number) => {
        treeData.push({
          title: htmlDecode(cat2.name),
          value: cat2.slug,
        });

        if (cat2.children.length > 0) {
          iterate(cat2, cat2.children[index]);
        }
      });
    };

    if (productCategories !== undefined && productCategories.length > 0) {
      productCategories.forEach((cat: any, index: number) => {
        treeData.push({
          title: htmlDecode(cat.name),
          value: cat.slug,
        });

        if (cat.children.length > 0) iterate(cat, treeData[index]);
      });

      // Append beginning
      treeData = [
        {
          title: "None",
          value: "",
          children: [],
        },
      ].concat(treeData);
    }

    return treeData;
  } catch (e) {
    // Display error AND
    // Return empty value AND
    console.log(e);
    return [];
  }
};
