import { connect } from "react-redux";
import { bindActionCreators, Dispatch } from "redux";
import { orderFormActions } from "store/actions";

// helper
import { htmlDecode } from "helpers/htmlDecode";
import { useEffect, useState } from "react";

const { setShowModal } = orderFormActions;

export interface IMetaData {
  key: React.Key;
  name: string;
  meta_key: string;
}

const DisplayMetaData = (props: any) => {
  const { product, metaKeys } = props;

  if (typeof metaKeys === "undefined" || metaKeys.length <= 0) return <></>;

  let elements: React.ReactNode[] = [];

  metaKeys.forEach((md: IMetaData, i: number) => {
    let indexes: number[] = [];
    product.meta_data.forEach((md2: any, j: number) => {
      if (md2.key === md.meta_key) {
        indexes.push(j);
      }
    });

    if (indexes.length > 0) {
      elements.push(
        <span key={i} style={{ display: "block" }}>
          <b>{md.name}</b>
          {`: ${indexes.map(
            (index: number) => " " + htmlDecode(product.meta_data[index].value)
          )}`}
        </span>
      );
    }
  });

  return <>{elements}</>;
};

const ProductMeta = (props: any) => {
  const {
    orderFormData,
    orderFormId,
    product,
    getPropValue,
    formStyles,
    itemId,
  } = props;

  const [productMeta, setProductMeta] = useState(product.meta_data ?? []);

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

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];

      if (
        typeof selectedProducts !== "undefined" &&
        Object.keys(selectedProducts).length > 0
      ) {
        const selectedVariation =
          orderFormData?.formSelectedProducts?.[orderFormId]?.[product.id]
            ?.variationID;

        const variations =
          orderFormData?.formProducts?.[orderFormId]?.["variations"]?.[
            product.id
          ];

        if (
          typeof selectedVariation !== "undefined" &&
          typeof variations !== "undefined"
        ) {
          const variationData = variations.find((variation: any) => {
            return variation.id === selectedVariation;
          });

          if (
            typeof variationData !== "undefined" &&
            typeof variationData.meta_data !== "undefined"
          ) {
            setProductMeta(variationData.meta_data);
          } else {
            setProductMeta(product.meta_data);
          }
        }
      } else {
        setProductMeta(product.meta_data);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  return (
    <div
      className="item product-meta"
      style={{ minWidth: "200px", ...alignmentCSS }}
    >
      <DisplayMetaData
        metaKeys={orderFormData?.formSettings?.[orderFormId]?.meta_data}
        productMeta={productMeta}
        product={product}
      />
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
export default connect(mapStateToProps, mapDispatchToProps)(ProductMeta);
