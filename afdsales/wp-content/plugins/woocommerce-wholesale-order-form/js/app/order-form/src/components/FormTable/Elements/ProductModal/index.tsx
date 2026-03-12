import { useState, useEffect } from "react";
import { Modal, Row, Col } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormActions } from "store/actions";
import DisplayVariations from "./DisplayVariations";
import AddToCart from "./AddToCart";
import DisplayQuantityBasedTable from "../shared/DisplayQuantityBasedTable";
import Categories from "./Categories";
import ProductImages from "./ProductImages";
import VariationAttributes from "../shared/VariationAttributes";
import { FrownOutlined } from "@ant-design/icons";

const { setShowModal } = orderFormActions;
declare var WWOF_Frontend_Options: any;

const Display = (props: any) => {
  const { value } = props;
  if (value) {
    return (
      <p
        style={{ fontSize: "16px" }}
        dangerouslySetInnerHTML={{
          __html: value,
        }}
      ></p>
    );
  } else return <></>;
};

const ProductModal = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    orderFormIdOrigin,
    product,
    showModal,
    onClickAction,
    getPropValue,
    actions,
    productModalStrings,
  } = props;

  const { setShowModal } = actions;
  const { unavailable } = productModalStrings;

  if (!showModal || orderFormId !== orderFormIdOrigin) return <></>;

  const [selectedVariation, setSelectedVariation] = useState(0);
  const [productName, setProductName] = useState(product.name);
  const [imageUrl, setImageUrl] = useState(
    WWOF_Frontend_Options.site_url +
      "/wp-content/uploads/woocommerce-placeholder-300x300.png"
  );
  const [priceHtml, setPriceHtml] = useState(product.price_html);
  const [shortDescription, setShortDescription] = useState(
    product.short_description
  );
  const [description, setDescription] = useState(product.description);

  const extraProps = {
    ...props,
    selectedVariation,
    setSelectedVariation,
    formStyles: orderFormData?.formStyles?.[orderFormId],
  };

  // Set selected variation
  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      if (
        typeof selectedProduct !== "undefined" &&
        typeof selectedProduct?.variationID !== "undefined"
      ) {
        setSelectedVariation(selectedProduct.variationID);
      } else {
        setSelectedVariation(0);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  useEffect(() => {
    try {
      const formProductVariations =
        orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
          product.id
        ];

      if (
        selectedVariation > 0 &&
        typeof formProductVariations !== "undefined"
      ) {
        const variation = formProductVariations.find((variation: any) => {
          return variation.id === selectedVariation;
        });

        if (typeof variation !== "undefined") {
          setProductName(
            typeof variation.name !== "undefined"
              ? variation.name
              : product.name
          );

          if (
            typeof variation?.images !== "undefined" &&
            variation?.images?.length > 0
          ) {
            setImageUrl(variation.images[0]["src"]);
          } else if (
            typeof variation.image !== "undefined" &&
            variation.image !== null
          ) {
            setImageUrl(variation.image["src"]);
          }

          if (typeof variation?.wholesale_data?.price_html !== "undefined") {
            let minOrderQty: any = "";
            let orderQtyStep: any = "";

            // Parent Min Order Qty and Step
            if (
              typeof product.wholesale_data !== "undefined" &&
              typeof variation.wholesale_data
                .wholesale_minimum_order_quantity === "undefined" &&
              typeof product.wholesale_data
                .variable_level_wholesale_minimum_order_quantity !== "undefined"
            ) {
              // Parent Min Order Qty
              if (
                typeof product.wholesale_data
                  .variable_level_wholesale_minimum_order_quantity[
                  WWOF_Frontend_Options.wholesale_role
                ] !== "undefined"
              ) {
                minOrderQty =
                  '<span class="wholesale_price_minimum_order_quantity" style="display: block">Min: ' +
                  product.wholesale_data
                    .variable_level_wholesale_minimum_order_quantity[
                    WWOF_Frontend_Options.wholesale_role
                  ] +
                  "</span>";
              }

              // Parent Order Step
              if (
                typeof product.wholesale_data
                  .variable_level_wholesale_order_quantity_step !==
                  "undefined" &&
                typeof product.wholesale_data
                  .variable_level_wholesale_order_quantity_step[
                  WWOF_Frontend_Options.wholesale_role
                ] !== "undefined"
              ) {
                orderQtyStep =
                  '<span class="wholesale_price_order_quantity_step" style="display: block;">Increments of ' +
                  product.wholesale_data
                    .variable_level_wholesale_order_quantity_step[
                    WWOF_Frontend_Options.wholesale_role
                  ] +
                  "</span>";
              }
            }
            setPriceHtml(
              variation.wholesale_data.price_html + minOrderQty + orderQtyStep
            );
          } else setPriceHtml(variation.price_html || variation.price);

          setShortDescription(variation.short_description || "");

          setDescription(variation.description || "");
        }
      } else {
        setProductName(product.name);

        if (product.images.length > 0) {
          setImageUrl(product.images[0]["src"]);
        } else {
          setImageUrl(
            WWOF_Frontend_Options.site_url +
              "/wp-content/uploads/woocommerce-placeholder-300x300.png"
          );
        }

        if (
          typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
          typeof product.wholesale_data !== "undefined" &&
          typeof product.wholesale_data.price_html !== "undefined" &&
          WWOF_Frontend_Options.wholesale_role !== ""
        ) {
          setPriceHtml(product.wholesale_data.price_html);
        } else {
          setPriceHtml(product.price_html || product.price);
        }

        setShortDescription(product.short_description || "");

        setDescription(product.description || "");
      }
    } catch (e) {
      console.log(e);
    }
  }, [selectedVariation]);

  let showQuantityBasedTable = getPropValue({
    formStyles: orderFormData.formStyles,
    item: "price",
    prop: "showQuantityBasedPricing",
  });

  if (showQuantityBasedTable === null) {
    showQuantityBasedTable = true;
  }

  return (
    <Modal
      className="responsive-modal"
      title=""
      visible={showModal}
      onCancel={() => {
        setShowModal({
          showModal: false,
          modalProps: {
            product: [],
            onClickAction: "",
          },
        });
      }}
      width={onClickAction === "show-image-popup" ? "350px" : "900px"}
      footer={null}
    >
      {onClickAction === "show-image-popup" ? (
        <ProductImages product={product} imageUrl={imageUrl} />
      ) : (
        <Row>
          <Col md={8} style={{ paddingRight: "1rem" }}>
            <ProductImages product={product} imageUrl={imageUrl} />
          </Col>
          <Col md={16} style={{ paddingRight: "1rem" }}>
            {/* Product Name */}
            <h2>{productName}</h2>

            {/* If Not Variable or if variable and have variations then display data else display unavailable text */}
            {product.type !== "variable" ||
            typeof orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
              product.id
            ] !== "undefined" ? (
              <>
                {/* Product Attributes */}
                <VariationAttributes product={product} />
                {/* Product Price */}
                <div style={{ textAlign: "justify" }}>
                  <Display value={priceHtml} />
                  <Display value={shortDescription} />
                  <Display value={description} />
                </div>
                {/* Product Price */}
                <DisplayVariations {...props} {...extraProps} />
                {/* Product Quantity Based Discoutn */}
                <DisplayQuantityBasedTable
                  {...props}
                  showQuantityBasedTable={showQuantityBasedTable}
                />
                <AddToCart {...props} {...extraProps} />
              </>
            ) : (
              <>
                {typeof WWOF_Frontend_Options.wholesale_role !== "undefined" ? (
                  <Display value={priceHtml} />
                ) : (
                  <></>
                )}
                <div className="stock-status" style={{ margin: "1rem 0px" }}>
                  <span className={product?.stock_status ?? "stock"}>
                    <FrownOutlined style={{ color: "#e2401c" }} /> {unavailable}
                  </span>
                </div>
              </>
            )}
            <Categories {...props} />
          </Col>
        </Row>
      )}
    </Modal>
  );
};

const mapStateToProps = (store: any) => ({
  showModal: store.orderForm.showModal,
  orderFormIdOrigin: store.orderForm.modalProps.orderFormId,
  product: store.orderForm.modalProps.product,
  onClickAction: store.orderForm.modalProps.onClickAction,
  orderFormData: store.orderFormData,
  productModalStrings: store.i18n.frontend.product_modal,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({ setShowModal }, dispatch),
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductModal);
