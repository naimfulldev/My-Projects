import { useState, useEffect } from "react";
import { Select } from "antd";
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormDataActions } from "store/actions";

const { setSelectedProductsToAddToCart } = orderFormDataActions;

const { Option } = Select;
declare var WWOF_Frontend_Options: any;

const StandardVariationsDropdown = (props: any) => {
  const {
    product,
    variations,
    orderFormId,
    orderFormData,
    setSelectedVariation,
    actions,
    standardStrings,
  } = props;

  const { setSelectedProductsToAddToCart } = actions;

  const [variationChanged, setVariationChanged] = useState(false);
  const [dropdownDefaults, setDropdownDefaults] = useState<any>([]);
  const [variationDataList, setVariationDataList] = useState<any>([]);
  const [selectedAtts, setSelectedAtts] = useState<any>({});

  useEffect(() => {
    try {
      const selectedProducts =
        orderFormData?.formSelectedProducts?.[orderFormId];
      const selectedProduct = selectedProducts?.[product.id];

      // Set selected variation id
      if (typeof selectedProduct?.selectedAtts !== "undefined") {
        setSelectedAtts(selectedProduct.selectedAtts);
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderFormData.formSelectedProducts[orderFormId]]);

  // Possible Dropdown Option Values
  useEffect(() => {
    if (Object.keys(selectedAtts).length === 0) {
      setVariationDataList(dropdownDefaults);
      return;
    }

    let avaiableAttributes = product.attributes.map((data: any, i: number) => {
      return data.name;
    });
    let selections: any = JSON.parse(JSON.stringify(selectedAtts));
    let foundAttributes: any = [];
    let variationDataCopy: any = dropdownDefaults.map((item: any) => ({
      ...item,
    }));

    let attributeCheck = (attributes: any, atts: any) => {
      let matchCounter = 0;

      atts.map((attr: any, i: number) => {
        let attrName = attr.toLowerCase();
        let attrValue = selections[attr].toLowerCase();

        for (const [, attr] of attributes.entries()) {
          let name = attr.name.toLowerCase();
          let option = attr.option.toLowerCase();

          if (attrName === name && attrValue === option) {
            matchCounter += 1;
          }
        }
      });

      return matchCounter === atts.length ? true : false;
    };

    if (Object.keys(selectedAtts).length === product.attributes.length) {
      Object.keys(selections).map((attr: any, i: number) => {
        let atts = Object.keys(selections).filter(
          (value: string, j: number, arr: string[]) => {
            return i !== j ? value : "";
          }
        );

        let foundAtts: any = [];
        for (const [, variation] of variations.entries()) {
          const attributes = variation.attributes;
          if (attributeCheck(attributes, atts)) {
            foundAtts = [...foundAtts, attributes];
          }
        }
        foundAttributes[attr] = [...foundAtts];
      });
    } else {
      let atts = avaiableAttributes.filter(
        (item: any) => Object.keys(selections).indexOf(item) == -1
      );

      atts.map((attr: any, i: number) => {
        let foundAtts: any = [];
        for (const [, variation] of variations.entries()) {
          const attributes = variation.attributes;
          if (attributeCheck(attributes, Object.keys(selections))) {
            foundAtts = [...foundAtts, attributes];
          }
        }
        foundAttributes[attr] = [...foundAtts];
      });
    }

    if (Object.keys(foundAttributes).length > 0) {
      variationDataCopy.map((data: any, i: number) => {
        let name = data[0];

        if (typeof foundAttributes[name] !== "undefined") {
          let choices = {};
          foundAttributes[name].map((data: any, i: number) => {
            data.map((data2: any, j: number) => {
              if (name == data2.name) {
                choices = { ...choices, [data2.option]: data2.option };
              }
            });
          });
          variationDataCopy[i][1] = { ...choices };
        }
      });
    }

    setVariationDataList(variationDataCopy);
  }, [selectedAtts]);

  // Variation Dropdown Change Event
  const variationChange = (label: string, value: string) => {
    setVariationChanged(true);
    const clone = JSON.parse(JSON.stringify(selectedAtts));

    if (value) {
      setSelectedAtts({
        ...clone,
        [label]: value,
      });
    } else {
      delete clone[label];
      setSelectedAtts(clone);
    }
  };

  // Find the variation ID based on the selected attributes
  const findVariationID = (selectedAtts: any) => {
    let searchVariation = (attributes: any, selectedAtts: any) => {
      let matchCounter = 0;
      Object.entries(selectedAtts).map((attr: any, i: number) => {
        let attrName = attr[0].toLowerCase();
        let attrValue = attr[1].toLowerCase();

        for (const [, attr] of attributes.entries()) {
          let name = attr.name.toLowerCase();
          let option = attr.option.toLowerCase();

          if (attrName === name && attrValue === option) {
            matchCounter++;
          }
        }
      });

      return matchCounter;
    };

    if (Object.keys(selectedAtts).length === product.attributes.length) {
      for (const [, variation] of variations.entries()) {
        const attributes = variation.attributes;
        if (
          searchVariation(attributes, selectedAtts) ===
          Object.keys(selectedAtts).length
        ) {
          return variation.id;
        }
      }
    }

    return 0;
  };

  // Hook on Selection Changed
  useEffect(() => {
    if (!variationChanged) return;

    let variationID = findVariationID(selectedAtts);

    const selectedProducts = orderFormData?.formSelectedProducts?.[orderFormId];
    const selectedProduct = selectedProducts?.[product.id];

    if (variationID > 0) {
      let quantity = 1;
      let stockStatus = product.stock_status;
      let wholesaleData: any = [];

      let variationData = [];

      // Set selected variation name
      const variation = variations.find((variation: any) => {
        return variation.id === variationID;
      });

      const name = variation.attributes.map((attributes: any) => {
        return attributes.name + " : " + attributes.option;
      });

      // Stock status
      if (typeof variation !== "undefined")
        stockStatus = variation.stock_status;

      // Wholesale Data
      if (
        typeof variation !== "undefined" &&
        typeof variation.wholesale_data !== "undefined"
      )
        variationData = variation.wholesale_data;

      if (typeof product.wholesale_data !== "undefined")
        wholesaleData = product.wholesale_data;

      // Set quantity if Wholesale Min Order Quantity is set
      if (
        typeof WWOF_Frontend_Options.wholesale_role !== "undefined" &&
        typeof product.wholesale_data !== "undefined"
      ) {
        if (
          typeof variation.wholesale_data.wholesale_minimum_order_quantity !==
            "undefined" &&
          variation.wholesale_data.wholesale_minimum_order_quantity[
            WWOF_Frontend_Options.wholesale_role
          ] !== ""
        ) {
          // Minimum Order Quantity is set in Simple/Variation
          quantity =
            variation.wholesale_data.wholesale_minimum_order_quantity[
              WWOF_Frontend_Options.wholesale_role
            ];
        } else if (
          typeof product.wholesale_data
            .variable_level_wholesale_minimum_order_quantity !== "undefined" &&
          product.wholesale_data
            .variable_level_wholesale_minimum_order_quantity[
            WWOF_Frontend_Options.wholesale_role
          ] !== ""
        ) {
          // Minimum Order Quantity is set in Variable
          quantity =
            product.wholesale_data
              .variable_level_wholesale_minimum_order_quantity[
              WWOF_Frontend_Options.wholesale_role
            ];
        }
      }

      if (typeof selectedProduct !== "undefined") {
        delete selectedProducts[product.id];

        setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...orderFormData.formSelectedProducts[orderFormId],
            [product.id]: {
              ...selectedProducts[product.id],
              productID: product.id,
              variationID,
              productTitle: product.name,
              productType: product.type,
              name: name.join("<br/>"),
              quantity,
              variationSelected: true,
              stockStatus,
              wholesaleData,
              variationData,
              selectedAtts,
            },
          },
        });

        // State From ProductModal
        if (typeof setSelectedVariation === "function")
          setSelectedVariation(variationID);
      } else {
        setSelectedProductsToAddToCart({
          [orderFormId]: {
            ...selectedProducts,
            [product.id]: {
              productID: product.id,
              variationID,
              productTitle: product.name,
              productType: product.type,
              name: name.join("<br/>"),
              quantity,
              variationSelected: true,
              stockStatus,
              wholesaleData,
              variationData,
              selectedAtts,
            },
          },
        });

        // State From ProductModal
        if (typeof setSelectedVariation === "function")
          setSelectedVariation(variationID);
      }
    } else {
      // Remove the product from selected products
      const formSelectedProductsCopy = selectedProducts;
      if (selectedProduct !== undefined) {
        // delete formSelectedProducts[product.id]
        formSelectedProductsCopy[product.id].variationID = 0;
        setSelectedProductsToAddToCart({
          ...selectedProducts,
          [orderFormId]: {
            ...selectedProducts,
            ...formSelectedProductsCopy,
          },
        });
      }

      // Current selected attributes
      setSelectedProductsToAddToCart({
        [orderFormId]: {
          ...selectedProducts,
          [product.id]: {
            ...selectedProduct,
            selectedAtts,
          },
        },
      });

      // State From ProductModal
      if (typeof setSelectedVariation === "function") setSelectedVariation(0);
    }
    setVariationChanged(false);
  }, [selectedAtts, variationChanged]);

  // Set default/initial dropdown data
  useEffect(() => {
    let variationData: any = {};

    for (const [, variation] of variations.entries()) {
      const attributes = variation.attributes;
      for (const [, attr] of attributes.entries()) {
        variationData[attr.name] = {
          ...variationData[attr.name],
          [attr.option]: attr.option,
        };
      }
    }

    variationData = Object.entries(variationData);

    setDropdownDefaults(variationData);
    setVariationDataList(variationData);
  }, []);

  return variationDataList.map((variation: any, i: number) => {
    let selectProps: any = {};

    if (
      Object.keys(selectedAtts).length > 0 &&
      typeof selectedAtts[variation[0]] !== "undefined"
    ) {
      selectProps = {
        value: selectedAtts[variation[0]],
      };
    }

    let label = variation[0];
    variation = Object.entries(variation[1]);

    const options = variation.map((attribute: any, i: number) => {
      return (
        <Option
          value={attribute[0].toLowerCase()}
          key={attribute[0]}
          className="standard-variation"
        >
          {attribute[0]}
        </Option>
      );
    });

    return (
      <div
        key={label}
        style={{ margin: "4px 0px" }}
        className="standard-dropdown"
      >
        <label htmlFor={label} style={{ display: "block" }}>
          {label}:{" "}
        </label>
        <Select
          {...selectProps}
          size="large"
          placeholder={standardStrings?.placeholder}
          style={{ textAlign: "left" }}
          filterOption={false}
          notFoundContent={standardStrings?.no_results}
          allowClear={true}
          onChange={(value: string) => {
            variationChange(label, value);
          }}
        >
          {options}
        </Select>
      </div>
    );
  });
};

const mapStateToProps = (store: any) => ({
  orderForm: store.orderForm,
  orderFormData: store.orderFormData,
  standardStrings: store.i18n.frontend.standard,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      setSelectedProductsToAddToCart,
    },
    dispatch
  ),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(StandardVariationsDropdown);
