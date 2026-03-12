import { useEffect, useState } from "react";
import { Select } from "antd";
import { connect } from "react-redux";

const { Option } = Select;

declare var WWOF_Frontend_Options: any;

const SortableColumns = (props: any) => {
  const {
    formTable,
    getPropValue,
    formStyles,
    fetchProducts,
    orderFormData,
    orderFormId,
    setTableSorter,
    tableColumnheadersStrings,
    sortableColumnStrings,
  } = props;
  const [sortBy, setSortBy] = useState("");
  const [sortOrder, setSortOrder] = useState("");

  const [sortableColumns, setSortableColumns] = useState<string[]>([]);
  const [sortOptions, setSortOptions] = useState<JSX.Element[]>([]);

  useEffect(() => {
    if (typeof formTable.itemIds !== "undefined") {
      let columnsToSort: string[] = [];
      formTable.itemIds.map((column: string) => {
        let alignment =
          getPropValue({
            formStyles,
            column,
            prop: "justifyContent",
          }) || "center";

        alignment =
          alignment === "flex-start"
            ? "left"
            : alignment === "flex-end"
            ? "right"
            : "center";

        let sortable = getPropValue({ formStyles, column, prop: "sortable" });

        if (sortable === null) sortable = true;

        if (sortable && ["product-name", "sku", "price"].includes(column)) {
          columnsToSort = [...columnsToSort, column];
        }
      });
      setSortableColumns(columnsToSort);
    }
  }, [formTable]);

  useEffect(() => {
    let options: JSX.Element[] = sortableColumns.map(
      (column: string, i: number) => {
        return (
          <Option value={column} key={i}>
            {tableColumnheadersStrings?.[column]}
          </Option>
        );
      }
    );
    setSortOptions(options);
  }, [sortableColumns]);

  useEffect(() => {
    try {
      if (sortBy !== "" && sortOrder !== "") {
        try {
          const sort_by = sortBy === "product-name" ? "title" : sortBy;
          const active_page =
            orderFormData?.formPagination?.[orderFormId]?.["active_page"] ?? 1;
          const per_page =
            orderFormData?.formSettings?.[orderFormId]?.["products_per_page"] ??
            10;

          const searchInput =
            orderFormData?.["formFilters"]?.[orderFormId]?.["searchInput"] ??
            "";

          const selectedCategory =
            orderFormData?.["formFilters"]?.[orderFormId]?.[
              "selectedCategory"
            ] ?? "";

          setTableSorter({
            [orderFormId]: {
              ...orderFormData["formTableSorter"][orderFormId],
              sort_order: sortOrder,
              sort_by,
            },
          });

          fetchProducts({
            orderFormData,
            sort_order: sortOrder,
            sort_by,
            search: searchInput,
            category: selectedCategory,
            active_page,
            searching: "no",
            show_all: false,
            attributes: { id: orderFormId },
            wholesale_role: WWOF_Frontend_Options.wholesale_role,
            per_page,
            form_settings: orderFormData.formSettings[orderFormId],
            uid: WWOF_Frontend_Options.uid,
          });
        } catch (e) {
          console.log(e);
        }
      }
    } catch (e) {
      console.log(e);
    }
  }, [sortBy, sortOrder]);

  if (sortableColumns.length > 0)
    return (
      <div className="sortable-columns" style={{ display: "none" }}>
        <Select
          size="large"
          placeholder={sortableColumnStrings?.sort_by}
          filterOption={false}
          notFoundContent={sortableColumnStrings?.sort_order}
          allowClear={true}
          onChange={(value: string) => {
            setSortBy(value);
          }}
        >
          {sortOptions}
        </Select>
        <Select
          size="large"
          placeholder={sortableColumnStrings?.sort_order}
          filterOption={false}
          notFoundContent={sortableColumnStrings?.no_results}
          allowClear={true}
          onChange={(value: string) => {
            setSortOrder(value);
          }}
        >
          <Option value="desc">{sortableColumnStrings?.desc}</Option>
          <Option value="asc">{sortableColumnStrings?.asc}</Option>
        </Select>
      </div>
    );
  else return <></>;
};

const mapStateToProps = (store: any, props: any) => ({
  tableColumnheadersStrings: store.i18n.frontend.table_column_headers,
  sortableColumnStrings: store.i18n.frontend.responsive_table.sortable_column,
});

export default connect(mapStateToProps)(SortableColumns);
