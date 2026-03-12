import { useEffect, useState } from "react";
import { Table, Popover, Tooltip } from "antd";
import { QuestionCircleOutlined } from "@ant-design/icons";
import { connect } from "react-redux";

declare var WWOF_Frontend_Options: any;

const setQtyTableDataSource = (mapping_data: any) => {
  try {
    let dataSource = [];
    for (var index in mapping_data) {
      let data = mapping_data[index];

      if (data.wholesale_role === WWOF_Frontend_Options.wholesale_role) {
        dataSource.push({
          key: index,
          qty:
            data.end_qty !== ""
              ? data.start_qty + " - " + data.end_qty
              : data.start_qty + "+",
          price: (
            <div dangerouslySetInnerHTML={{ __html: data.calculated_price }} />
          ),
        });
      }
    }

    return dataSource;
  } catch (e) {
    console.log(e);
    return undefined;
  }
};

const DisplayQuantityBasedTable = (props: any) => {
  const {
    product,
    showModal,
    showQuantityBasedTable,
    orderFormData,
    orderFormId,
    displayQtyBasedTableStrings,
  } = props;

  const [description, setDescription] = useState("");
  const [dataSource, setDataSource] = useState<any[]>();

  useEffect(() => {
    try {
      const qtyDiscountRule =
        product?.wholesale_data?.wwpp_enable_quantity_discount_rule;
      const qtyDiscountMapping =
        product?.wholesale_data?.wwpp_quantity_discount_rule_mapping;

      if (
        typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
        typeof product.wholesale_data !== "undefined" &&
        typeof product.wholesale_data.wwpp_enable_quantity_discount_rule !==
          "undefined" &&
        product.wholesale_data.wwpp_enable_quantity_discount_rule === true &&
        WWOF_Frontend_Options.wholesale_role !== ""
      ) {
        if (
          typeof qtyDiscountMapping !== "undefined" &&
          typeof qtyDiscountMapping?.mapping !== "undefined"
        ) {
          setDescription(qtyDiscountMapping.desc);
          setDataSource(setQtyTableDataSource(qtyDiscountMapping.mapping));
        }
      }
    } catch (e) {
      console.log(e);
    }
  }, [WWOF_Frontend_Options.wholesale_role]);

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        product.type === "variable" &&
        typeof selectedProduct?.variationID !== "undefined" &&
        selectedProduct.variationID > 0
      ) {
        const variationID = selectedProduct.variationID;
        const variations =
          orderFormData.formProducts[orderFormId]["variations"][product.id];

        if (
          typeof variations !== "undefined" &&
          typeof variationID !== "undefined"
        ) {
          const variationData = variations.find((variation: any) => {
            return variation.id === variationID;
          });

          if (
            typeof variationData !== "undefined" &&
            typeof variationData.wholesale_data !== "undefined"
          ) {
            const qtyDiscountRuleMapping =
              variationData?.wholesale_data
                ?.wwpp_quantity_discount_rule_mapping;

            if (
              typeof qtyDiscountRuleMapping !== "undefined" &&
              typeof qtyDiscountRuleMapping?.mapping !== "undefined"
            ) {
              setDescription(qtyDiscountRuleMapping.desc);
              setDataSource(
                setQtyTableDataSource(qtyDiscountRuleMapping.mapping)
              );
            } else {
              setDescription("");
              setDataSource(undefined);
            }
          }
        }
      } else {
        const qtyDiscountRule =
          product?.wholesale_data?.wwpp_enable_quantity_discount_rule;
        const qtyDiscountMapping =
          product?.wholesale_data?.wwpp_quantity_discount_rule_mapping;

        if (
          typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
          typeof product.wholesale_data !== "undefined" &&
          typeof product.wholesale_data.wwpp_enable_quantity_discount_rule !==
            "undefined" &&
          product.wholesale_data.wwpp_enable_quantity_discount_rule === true &&
          WWOF_Frontend_Options.wholesale_role !== ""
        ) {
          if (
            typeof qtyDiscountMapping !== "undefined" &&
            typeof qtyDiscountMapping?.mapping !== "undefined"
          ) {
            setDescription(qtyDiscountMapping.desc);
            setDataSource(setQtyTableDataSource(qtyDiscountMapping.mapping));
          } else {
            setDescription("");
            setDataSource(undefined);
          }
        } else {
          setDescription("");
          setDataSource(undefined);
        }
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  const columns = [
    {
      title: "Qty",
      dataIndex: "qty",
      key: "qty",
    },
    {
      title: "Price",
      dataIndex: "price",
      key: "price",
    },
  ];

  if (
    !showQuantityBasedTable ||
    typeof dataSource === "undefined" ||
    dataSource.length === 0
  )
    return <></>;

  if (showModal === true) {
    return (
      <>
        <div
          style={{ marginBottom: "1rem" }}
          className="qty-based-discount-table-desc"
          dangerouslySetInnerHTML={{
            __html: description,
          }}
        />
        <Table
          className="wwof-qty-based-table"
          style={{
            width: "auto",
            minWidth: "auto",
            margin: "1rem 0rem",
          }}
          dataSource={dataSource}
          columns={columns}
          pagination={false}
          scroll={undefined}
        />
      </>
    );
  } else {
    return (
      <Popover
        className="qty-based-discount"
        content={
          <>
            <div
              style={{ marginBottom: "1rem" }}
              className="qty-based-discount-table-desc"
              dangerouslySetInnerHTML={{
                __html: description,
              }}
            />
            <Table
              className="wwof-qty-based-table"
              style={{ width: "auto", minWidth: "auto", marginBottom: "15px" }}
              dataSource={dataSource}
              columns={columns}
              pagination={false}
              scroll={undefined}
            />
          </>
        }
        title={displayQtyBasedTableStrings?.popover_title}
        trigger="click"
      >
        <Tooltip title={displayQtyBasedTableStrings?.tooltip}>
          <QuestionCircleOutlined />
        </Tooltip>
      </Popover>
    );
  }
};

const mapStateToProps = (store: any) => ({
  displayQtyBasedTableStrings: store.i18n.frontend.display_qty_based_table,
});

export default connect(mapStateToProps)(DisplayQuantityBasedTable);
