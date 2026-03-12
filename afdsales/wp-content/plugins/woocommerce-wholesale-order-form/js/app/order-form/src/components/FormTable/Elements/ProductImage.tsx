import { useState, useEffect } from "react";
import { Avatar } from "antd";
import { connect } from "react-redux";
import { bindActionCreators, Dispatch } from "redux";
import { orderFormActions } from "store/actions";

const { setShowModal } = orderFormActions;
declare var WWOF_Frontend_Options: any;

const ProductImage = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
    actions,
  } = props;
  const { setShowModal } = actions;

  const [url, setUrl] = useState(
    WWOF_Frontend_Options.product_image_placeholder
  );

  useEffect(() => {
    try {
      if (product.images.length > 0) {
        setUrl(product.images[0]["src"]);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formPagination[orderFormId]]);

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      if (
        typeof selectedProducts !== "undefined" &&
        Object.keys(selectedProducts).length > 0
      ) {
        if (
          typeof selectedProducts?.[product.id] !== "undefined" &&
          product.type === "variable"
        ) {
          const variationID = selectedProducts?.[product.id]["variationID"];
          const variations =
            orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
              product.id
            ];

          if (typeof variations !== "undefined") {
            const variationData = variations.find((variation: any) => {
              return variation.id === variationID;
            });

            if (typeof variationData !== "undefined") {
              if (
                typeof variationData.images !== "undefined" &&
                variationData.images.length > 0
              ) {
                setUrl(variationData.images[0]["src"]);
              } else if (
                typeof variationData.image !== "undefined" &&
                variationData.image !== null
              ) {
                setUrl(variationData.image["src"]);
              }
            }
          }
        }
      } else {
        if (product.images.length > 0) {
          setUrl(product.images[0]["src"]);
        } else {
          setUrl(
            WWOF_Frontend_Options.site_url +
              "/wp-content/uploads/woocommerce-placeholder-300x300.png"
          );
        }
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  const alignment =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "justifyContent",
    }) || "center";

  const alignmentCSS = {
    textAlign:
      alignment === "flex-start"
        ? ("left" as const)
        : alignment === "flex-end"
        ? ("right" as const)
        : ("center" as const),
  };

  const imageSize = getPropValue({
    formStyles,
    item: itemId,
    prop: "imageSize",
  }) || { width: 48, height: 48 };

  const onClickAction =
    getPropValue({
      formStyles,
      item: itemId,
      prop: "onClick",
    }) || "show-product-details";

  return (
    <div
      className="item product-image"
      style={{ minWidth: "100px", ...alignmentCSS }}
    >
      <a
        style={{ height: "auto" }}
        type="link"
        onClick={(e: any) => {
          if (
            onClickAction === "show-product-details" ||
            onClickAction === "show-image-popup"
          ) {
            setShowModal({
              showModal: true,
              modalProps: {
                orderFormId,
                product,
                onClickAction,
              },
            });
          } else {
            window.location.href = product.permalink;
          }
        }}
      >
        <Avatar
          src={url}
          shape="square"
          style={{
            width: imageSize.width + "px",
            height: imageSize.height + "px",
          }}
        />
      </a>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({ setShowModal }, dispatch),
});

export default connect(mapStateToProps, mapDispatchToProps)(ProductImage);
