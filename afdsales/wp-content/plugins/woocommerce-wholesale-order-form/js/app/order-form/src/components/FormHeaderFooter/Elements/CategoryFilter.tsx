import { useState, useEffect } from "react";
import { TreeSelect, Select } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

// Actions
import { orderFormDataActions } from "store/actions";

// helper
import { htmlDecode } from "helpers/htmlDecode";

const { Option } = Select;
const { SHOW_PARENT } = TreeSelect;

const { setFormFilters } = orderFormDataActions;

const CategoryFilter = (props: any) => {
  const {
    getPropValue,
    properties,
    orderFormId,
    fetchProducts,
    submitOnChange,
    defaultCategory,
    includedCategories,
    excludedCategories,
    orderForm,
    orderFormData,
    styles,
    actions,
    categoryFilterStrings,
  } = props;

  const { setFormFilters } = actions;
  const { categories } = orderForm;

  const searchInput =
    orderFormData?.["formFilters"]?.[orderFormId]?.["searchInput"] ?? "";

  const [dropdownData, setdropdownData] = useState<any>(""); // For hierarchal dropdown
  const [categoryDataList, setCategoryDataList] = useState([]); // List of categories non-hierarchal/no children
  const [selectedValue, setSelectedValue] = useState<any>(""); // The default category name

  const placeholder =
    getPropValue({ properties, prop: "placeholder" }) ??
    categoryFilterStrings?.placeholder;

  let filteredCategory: any = [];

  if (includedCategories !== null && includedCategories.length > 0) {
    filteredCategory = includedCategories.filter((val: any) =>
      excludedCategories !== null ? !excludedCategories.includes(val) : true
    );
  }

  // Setting Hierarchal and one level array of categories
  useEffect(() => {
    let treeData: any[] = [];

    let iterate = (cat: any, data: any) => {
      cat.children.forEach((cat2: any, index: number) => {
        if (
          excludedCategories === null ||
          (excludedCategories !== null &&
            !excludedCategories.includes(cat2.slug) &&
            typeof data !== "undefined")
        ) {
          let i = data.children.push({
            title: htmlDecode(cat2.name),
            value: cat2.slug,
            children: [],
          });
          if (cat2.children.length > 0) {
            iterate(cat2, data.children[i - 1]);
          }
        }
      });
    };

    if (categories !== undefined && categories.length > 0) {
      categories.forEach((cat: any, index: number) => {
        if (
          excludedCategories === null ||
          (excludedCategories !== null &&
            !excludedCategories.includes(cat.slug))
        ) {
          let i = treeData.push({
            title: htmlDecode(cat.name),
            value: cat.slug,
            children: [],
          });
          if (cat.children.length > 0) iterate(cat, treeData[i - 1]);
        }
      });

      // Append beginning
      setdropdownData(
        [
          {
            title: placeholder,
            value: placeholder,
            children: [],
          },
        ].concat(treeData)
      );
    }
  }, [categories]);

  useEffect(() => {
    let catData: any = [];

    let iterate = (cat: any) => {
      cat.children.forEach((cat2: any, index: number) => {
        catData[cat2.slug] = htmlDecode(cat2.name);
        if (cat2.children.length > 0) {
          iterate(cat2);
        }
      });
    };

    if (categories !== undefined && categories.length > 0) {
      categories.forEach((cat: any, index: number) => {
        catData[cat.slug] = htmlDecode(cat.name);
        if (cat.children.length > 0) iterate(cat);
      });
    }
    setCategoryDataList(catData);
  }, [categories]);

  // Setting Selected Value
  useEffect(() => {
    if (Object.keys(categoryDataList).length === 0) return;
    if (filteredCategory.length > 0) {
      if (filteredCategory.includes(defaultCategory)) {
        setSelectedValue(categoryDataList[defaultCategory]);
        setFormFilters({
          [orderFormId]: {
            ...orderFormData["formFilters"][orderFormId],
            selectedCategory: defaultCategory,
          },
        });
      } else {
        setSelectedValue("");
        setFormFilters({
          [orderFormId]: {
            ...orderFormData["formFilters"][orderFormId],
            selectedCategory: "",
            categoryComponentLoaded: true,
          },
        });
      }
    } else if (
      typeof defaultCategory !== "undefined" &&
      typeof categoryDataList[defaultCategory] !== "undefined"
    ) {
      setSelectedValue(categoryDataList[defaultCategory]);
      setFormFilters({
        [orderFormId]: {
          ...orderFormData["formFilters"][orderFormId],
          selectedCategory: defaultCategory,
        },
      });
    } else {
      setSelectedValue("");
      setFormFilters({
        [orderFormId]: {
          ...orderFormData["formFilters"][orderFormId],
          selectedCategory: "",
          categoryComponentLoaded: true,
        },
      });
    }
  }, [categoryDataList, includedCategories, excludedCategories]);

  // Reset category value to empty
  useEffect(() => {
    if (
      orderFormData?.["formFilters"]?.[orderFormId]?.["searchInput"] === "" &&
      orderFormData?.["formFilters"]?.[orderFormId]?.["selectedCategory"] === ""
    ) {
      setSelectedValue("");
    }
  }, [orderFormData["formFilters"][orderFormId]]);

  const onChange = (categoryName: string, treeData: Array<any>) => {
    try {
      if (categoryName) {
        setSelectedValue(categoryName);
        setFormFilters({
          [orderFormId]: {
            ...orderFormData["formFilters"][orderFormId],
            selectedCategory: categoryName,
          },
        });
      } else {
        setSelectedValue("");
        setFormFilters({
          [orderFormId]: {
            ...orderFormData["formFilters"][orderFormId],
            selectedCategory: "",
          },
        });
      }

      if (submitOnChange) {
        fetchProducts({
          orderFormData,
          search: searchInput,
          category: categoryName,
          active_page: 1,
          searching: "yes",
        });
      }
    } catch (e) {
      console.log(e);
    }
  };

  let extraProps = {};
  if (selectedValue !== "") {
    extraProps = { value: selectedValue };
  }

  // Included - Excluded Categories
  // Show normal dropdown
  if (filteredCategory.length > 0) {
    // filteredCategory = [placeholder].concat(filteredCategory);
    let allOptions = filteredCategory.map((catSlug: any, index: number) => {
      return (
        <Option key={index} value={catSlug}>
          {typeof categoryDataList[catSlug] !== "undefined"
            ? categoryDataList[catSlug]
            : catSlug}
        </Option>
      );
    });
    return (
      <Select
        size="large"
        {...extraProps}
        showSearch={true}
        placeholder={placeholder}
        allowClear={true}
        style={{
          width: "250px",
          ...styles,
        }}
        onChange={(slug: any) => {
          onChange(slug, dropdownData);
        }}
      >
        {allOptions}
      </Select>
    );
  } else {
    return (
      <>
        <TreeSelect
          size="large"
          {...extraProps}
          showSearch
          allowClear
          className="wwof-category-filter"
          treeData={dropdownData}
          placeholder={placeholder}
          treeDefaultExpandAll
          showCheckedStrategy={SHOW_PARENT}
          style={{
            width: "250px",
            ...styles,
          }}
          onChange={(val: string) => onChange(val, dropdownData)}
        />
      </>
    );
  }
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  filter: store.filter,
  categoryFilterStrings: store.i18n.frontend.category_filter,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setFormFilters,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(CategoryFilter);
