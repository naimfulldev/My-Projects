import { useState, useEffect, createRef, useCallback } from "react";
import { Table, Checkbox, Spin } from "antd";
import PrintItem from "./PrintItem";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormDataActions, orderFormActions } from "store/actions/";
import ResponsiveTable from "./ResponsiveTable";
import useWindowDimensions from "helpers/useWindowDimensions";
import useFormTableData from "helpers/useFormTableData";
import ProductModal from "./Elements/ProductModal";

const { fetchProducts, loadMoreProducts } = orderFormActions;
const { updateSelectedProducts, setTableSorter } = orderFormDataActions;

declare var WWOF_Frontend_Options: any;
declare var window: any;

const useStyleValue = (style: any) => {
  let val = "auto";

  if (style === "") return val;

  if (style.type === "auto" || style.type === "inherit") return val;

  if (style.value !== undefined)
    return style.type === "pixels" ? style.value + "px" : style.value + "%";

  return val;
};

export const getPropValue = (props: any) => {
  const { formStyles, item, prop } = props;

  const propValue = formStyles?.[item]?.["props"]?.[prop];

  return typeof propValue !== "undefined" ? propValue : null;
};

const FormTable = (props: any) => {
  const {
    orderFormId,
    orderFormData,
    actions,
    formTableStrings,
    form_table_sort_text,
  } = props;
  const {
    updateSelectedProducts,
    fetchProducts,
    loadMoreProducts,
    setTableSorter,
  } = actions;

  const [selectedAll, setSelectedAll] = useState(false);
  const [selected, setSelected] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [somethingChanged, setSomethingChanged] = useState(false);
  const [pageHasVariations, setPageHasVariations] = useState(false);

  // Responsive width
  const { width } = useWindowDimensions();

  // Lazy Load
  const [loadMore, setLoadMore] = useState(false);
  const orderFormTableWrapper = createRef<HTMLDivElement>();
  const [loadMoreWidth, setLoadMoreWidth] = useState(0);
  const [isLazyLoadEnabled, setIsLazyLoadEnabled] = useState(false);

  const [dataSource, setDataSource] = useState([]);
  const [widthType, setWidthType] = useState("");
  const [tableCSS, setTableCSS] = useState({});
  const [tableProps, setTableProps] = useState({});
  const [tableColumns, setTableColumns] = useState([]);

  // Form Products Data
  const { fetching, formProducts, formStyles, formTable } = useFormTableData({
    orderFormData,
    orderFormId,
  });

  useEffect(() => {
    const lazyLoading =
      orderFormData?.formSettings?.[orderFormId]?.["lazy_loading"];

    if (typeof lazyLoading !== "undefined") {
      setIsLazyLoadEnabled(lazyLoading);
    }
  }, [orderFormData.formSettings]);

  // Get form table wrapper width and assign to the loader width. In case the table width is full or auto loader should follow the width.
  useEffect(() => {
    if (orderFormTableWrapper.current !== null) {
      setLoadMoreWidth(orderFormTableWrapper.current.clientWidth);
    }
  }, [orderFormTableWrapper]);

  // Lazy load on scroll - Load more products when the last row of the table is visible on the screen
  const handleScroll = useCallback(() => {
    const tableWrapper: any = document.querySelector(
      ".order-form-table-" + orderFormId
    );

    const lastRow: any = document.querySelector(
      ".order-form-table-" + orderFormId + " table tbody tr:last-child"
    );

    if (tableWrapper !== null && lastRow !== null) {
      var position = lastRow.getBoundingClientRect();
      // Last row is visible on screen. Trigger loading more products.
      if (position.top < window.innerHeight && position.bottom >= 0) {
        const formPagination = orderFormData?.formPagination?.[orderFormId];
        const active_page = formPagination?.active_page ?? 0;
        const total_page = formPagination?.total_page ?? 1;
        const per_page = formPagination?.per_page ?? 10;

        // Fetch 1 at a time
        if (
          active_page > 0 &&
          active_page < total_page &&
          (tableWrapper.getAttribute("loading-more") === null ||
            tableWrapper.getAttribute("loading-more") === "no")
        ) {
          tableWrapper.setAttribute("loading-more", "yes");
          setLoadMore(true);
          loadMoreProducts({
            orderFormId,
            orderFormData,
            active_page: active_page + 1,
            per_page,
            wholesale_role: WWOF_Frontend_Options.wholesale_role,
            successCB: (response: any) => {
              setLoadMore(false);
              tableWrapper.setAttribute("loading-more", "no");
            },
            failCB: (response: any) => {
              setLoadMore(false);
              tableWrapper.setAttribute("loading-more", "no");
            },
          });
        }
      }
    }
  }, [
    loadMoreProducts,
    orderFormData,
    orderFormData.formPagination,
    orderFormId,
  ]);

  // Lazy loading scroll event
  useEffect(() => {
    if (isLazyLoadEnabled) {
      handleScroll(); // On load check if the bottom row is visible
      window.addEventListener("scroll", handleScroll); // On scroll check if bottom row is visible
      return () => window.removeEventListener("scroll", handleScroll);
    }
  }, [handleScroll]);

  // Uncheck Selected All
  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      if (
        typeof selectedProducts !== "undefined" &&
        Object.keys(selectedProducts).length <= 0
      ) {
        setSelectedAll(false);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts, orderFormId]);

  // If select all was checked
  useEffect(() => {
    try {
      if (formTable !== undefined && somethingChanged) {
        setSomethingChanged(false);
        updateSelectedProducts({
          selected: selectedAll,
          orderFormData,
          orderFormId,
        });
      }
    } catch (e) {
      console.log(e);
    }
  }, [selectedAll]);

  // When page is changed check if the page has selected all state
  useEffect(() => {
    try {
      const formPagination = orderFormData?.formPagination?.[orderFormId];
      if (
        typeof formPagination !== "undefined" &&
        typeof formPagination?.active_page !== "undefined"
      ) {
        setCurrentPage(formPagination?.active_page);

        if (
          typeof formPagination?.selectedAll !== "undefined" &&
          typeof formPagination?.selectedAll[currentPage] !== "undefined"
        ) {
          setSelected(formPagination?.selectedAll[currentPage]);
          setSelectedAll(formPagination?.selectedAll[currentPage]);
        }
      }
    } catch (e) {
      console.log(e);
    }
  }, [currentPage, orderFormData.formPagination, orderFormId, selectedAll]);

  // When select all was checked save state
  useEffect(() => {
    try {
      const formPagination = orderFormData?.formPagination?.[orderFormId];
      if (
        typeof formPagination !== "undefined" &&
        typeof formPagination?.selectedAll !== "undefined" &&
        typeof formPagination?.selectedAll[currentPage] !== "undefined"
      ) {
        setSelected(formPagination?.selectedAll?.[currentPage]);
        setSelectedAll(formPagination?.selectedAll?.[currentPage]);
      } else {
        setSelected(false);
        setSelectedAll(false);
      }
    } catch (e) {
      console.log(e);
    }
  }, [currentPage, orderFormData.formPagination, orderFormId]);

  // Check if the page has variations available
  // Used for variation dropdown table column "Smart Visibility"
  useEffect(() => {
    try {
      const variations =
        orderFormData?.formProducts?.[orderFormId]?.["variations"];
      if (
        typeof variations !== "undefined" &&
        Object.entries(variations).length > 0
      ) {
        setPageHasVariations(true);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formProducts, orderFormId]);

  // Table Data Source
  useEffect(() => {
    const data: any =
      formProducts !== undefined && formProducts.length > 0
        ? formProducts.map((product: any, i: number) => {
            const printItemProps = {
              orderFormId,
              product,
              getPropValue,
              formStyles,
              useStyleValue,
            };

            return {
              key: product.id,
              "product-image": (
                <PrintItem {...printItemProps} itemId="product-image" />
              ),
              "product-name": (
                <PrintItem {...printItemProps} itemId="product-name" />
              ),
              sku: <PrintItem {...printItemProps} itemId="sku" />,
              "in-stock-amount": (
                <PrintItem {...printItemProps} itemId="in-stock-amount" />
              ),
              price: <PrintItem {...printItemProps} itemId="price" />,
              "quantity-input": (
                <PrintItem {...printItemProps} itemId="quantity-input" />
              ),
              "add-to-cart-button": (
                <PrintItem {...printItemProps} itemId="add-to-cart-button" />
              ),
              "variation-dropdown": (
                <PrintItem {...printItemProps} itemId="variation-dropdown" />
              ),
              "product-meta": (
                <PrintItem {...printItemProps} itemId="product-meta" />
              ),
              "global-attribute": (
                <PrintItem {...printItemProps} itemId="global-attribute" />
              ),
              "short-description": (
                <PrintItem {...printItemProps} itemId="short-description" />
              ),
              "add-to-cart-checkbox": (
                <PrintItem
                  {...printItemProps}
                  selectedAll={selectedAll}
                  somethingChanged={somethingChanged}
                  itemId="add-to-cart-checkbox"
                />
              ),
            };
          })
        : [];

    setDataSource(data);
  }, [formProducts, selectedAll]);

  useEffect(() => {
    if (formTable === undefined || formTable.length <= 0) return;
    let table: any = formTable;

    const columns: any = Object.keys(table["itemIds"]).map((key: any) => {
      const item = table["itemIds"][key];

      let alignment =
        getPropValue({
          formStyles,
          item,
          prop: "justifyContent",
        }) || "center";

      alignment =
        alignment === "flex-start"
          ? "left"
          : alignment === "flex-end"
          ? "right"
          : "center";

      let sortable = getPropValue({ formStyles, item, prop: "sortable" });

      if (sortable === null) sortable = true;

      switch (item) {
        case "product-image":
          return {
            width: 100,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "product-image",
            key: "product-image",
          };
        case "product-name":
          return {
            width: 200,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "product-name",
            key: "product-name",
            sorter: sortable,
          };
        case "sku":
          return {
            width: 100,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "sku",
            key: "sku",
            sorter: sortable,
          };
        case "in-stock-amount":
          return {
            width: 100,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "in-stock-amount",
            key: "in-stock-amount",
          };
        case "price":
          return {
            width: 150,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "price",
            key: "price",
            sorter: sortable,
          };
        case "quantity-input":
          return {
            width: 100,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "quantity-input",
            key: "quantity-input",
          };
        case "add-to-cart-button":
          return {
            width: 100,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "add-to-cart-button",
            key: "add-to-cart-button",
          };
        case "variation-dropdown":
          let smartVisibility = getPropValue({
            formStyles,
            item,
            prop: "smartVisibility",
          });
          if (smartVisibility === undefined || smartVisibility === null) {
            smartVisibility = true;
          }

          if (!pageHasVariations && smartVisibility) {
            return {};
          } else {
            return {
              width: 250,
              align: alignment,
              title:
                getPropValue({ formStyles, item, prop: "columnHeading" }) ||
                formTableStrings?.[item],
              dataIndex: "variation-dropdown",
              key: "variation-dropdown",
            };
          }
        case "product-meta":
          return {
            width: 100,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "product-meta",
            key: "product-meta",
          };
        // case "global-attribute":
        //   return {
        //     width: 100,
        //     align: alignment,
        //     title:
        //       getPropValue({ formStyles, item, prop: "columnHeading" }) || "",
        //     dataIndex: "global-attribute",
        //     key: "global-attribute",
        //   };
        case "short-description":
          return {
            width: 250,
            align: alignment,
            title:
              getPropValue({ formStyles, item, prop: "columnHeading" }) ||
              formTableStrings?.[item],
            dataIndex: "short-description",
            key: "short-description",
          };
        case "add-to-cart-checkbox":
          return {
            width: 100,
            align: "center",
            title: (
              <Checkbox
                checked={selected}
                onChange={() => {
                  setSelectedAll(!selectedAll);
                  setSomethingChanged(true);
                }}
              />
            ),
            dataIndex: "add-to-cart-checkbox",
            key: "add-to-cart-checkbox",
          };
        default:
          return {};
      }
    });

    // Filter empty column
    setTableColumns(
      columns.filter((value: any) => Object.keys(value).length !== 0)
    );
  }, [formTable, pageHasVariations, selected]);

  useEffect(() => {
    try {
      let styles: any = formStyles;
      let tProps: any = {
        loading: fetching,
        dataSource: tableColumns.length > 0 ? dataSource : [],
        columns: tableColumns,
        pagination: false,
        scroll: { x: "100%" },
      };

      let css = {};
      let type = "full-width";

      if (typeof styles?.["formTable"]?.["box"] !== "undefined") {
        type = styles["formTable"]["box"]["width"]["type"];
      }

      if (type === "full-width") {
        css = { flexGrow: 1 };
      }

      setTableProps(tProps);
      setWidthType(type);
      setTableCSS(css);
    } catch (e) {
      console.log(e);
    }
  }, [tableColumns, dataSource, fetching, formStyles]);

  const handleSorting = (pagination: any, filters: any, sorter: any) => {
    try {
      const sortBy = sorter.field === "product-name" ? "title" : sorter.field;

      const activePage =
        orderFormData?.formPagination?.[orderFormId]?.["active_page"] ?? 1;

      const perPage =
        orderFormData?.formSettings?.[orderFormId]?.["products_per_page"] ?? 10;

      const searchInput =
        orderFormData?.["formFilters"]?.[orderFormId]?.["searchInput"] ?? "";

      const selectedCategory =
        orderFormData?.["formFilters"]?.[orderFormId]?.["selectedCategory"] ??
        "";

      if (typeof sorter.order !== "undefined") {
        setTableSorter({
          [orderFormId]: {
            ...orderFormData["formTableSorter"][orderFormId],
            sort_order: sorter.order === "ascend" ? "asc" : "desc",
            sort_by: sortBy,
          },
        });
      } else {
        setTableSorter({ [orderFormId]: {} });
      }
      fetchProducts({
        orderFormData,
        sort_order: sorter.order === "ascend" ? "asc" : "desc",
        sort_by: sortBy,
        search: searchInput,
        category: selectedCategory,
        active_page: activePage,
        searching: "no",
        show_all: false,
        attributes: { id: orderFormId },
        wholesale_role: WWOF_Frontend_Options.wholesale_role,
        per_page: perPage,
        form_settings: orderFormData.formSettings[orderFormId],
        uid: WWOF_Frontend_Options.uid,
      });
    } catch (e) {
      console.log(e);
    }
  };

  return (
    <>
      <ProductModal
        orderFormData={orderFormData}
        orderFormId={orderFormId}
        getPropValue={getPropValue}
      />
      <div
        ref={orderFormTableWrapper}
        className="order-form-table-wrapper"
        style={{
          display: widthType === "full-width" ? "flex" : "inline-block",
          width: "auto",
          marginBottom: "1rem",
        }}
      >
        {width <= 768 ? (
          <ResponsiveTable
            key={pageHasVariations}
            {...props}
            getPropValue={getPropValue}
            pageHasVariations={pageHasVariations}
            useStyleValue={useStyleValue}
            somethingChanged={somethingChanged}
            formProducts={formProducts}
            formStyles={formStyles}
            formTable={formTable}
            fetching={fetching}
            fetchProducts={fetchProducts}
            setTableSorter={setTableSorter}
            selected={selected}
            selectedAll={selectedAll}
            setSelectedAll={setSelectedAll}
            setSomethingChanged={setSomethingChanged}
            // modalProps={modalProps}
            // setModalProps={setModalProps}
          />
        ) : (
          <Table
            style={{
              width: "auto",
              ...tableCSS,
            }}
            className={`wwof-order-form order-form-table-${orderFormId}`}
            {...tableProps}
            onChange={(pagination: any, filters: any, sorter: any) =>
              handleSorting(pagination, filters, sorter)
            }
            locale={{
              triggerDesc:
                form_table_sort_text?.["sort_desc"] ??
                "Click to sort descending",
              triggerAsc:
                form_table_sort_text?.["sort_asc"] ?? "Click to sort ascending",
              cancelSort:
                form_table_sort_text?.["sort_cancel"] ??
                "Click to cancel sorting",
            }}
          />
        )}
      </div>
      {/* Loader - Load more products / Lazy load on scroll */}
      {loadMore ? (
        <Spin
          tip="Loading..."
          style={{
            width: loadMoreWidth > 0 ? loadMoreWidth : "auto",
            paddingBottom: "20px",
          }}
        />
      ) : (
        ""
      )}
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  orderFormData: store.orderFormData,
  formTableStrings: store.i18n.frontend.table_column_headers,
  form_table_sort_text: store.i18n.frontend.form_table_sort_text,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      updateSelectedProducts,
      fetchProducts,
      setTableSorter,
      loadMoreProducts,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(FormTable);
