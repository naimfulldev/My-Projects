import React from "react";
import DisplayItems from "./DisplayItems";

import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { readableStyles } from "helpers/readableStyles";
import { getPropValue } from "helpers/getPropValue";

// Actions
import { orderFormActions } from "store/actions";
const { fetchProducts } = orderFormActions;

declare var WWOF_Frontend_Options: any;

const FormHeaderFooter = React.memo((props: any) => {
  const {
    orderFormId,
    section,
    formHeader,
    formFooter,
    orderFormData,
    actions,
    formStyles,
  } = props;
  const { fetchProducts } = actions;

  const sectionStyles =
    formStyles !== undefined && formStyles[section] !== undefined
      ? formStyles[section]
      : {};

  let showHeader = getPropValue({
    formStyles,
    item: section,
    prop: "showFormHeader",
  });

  let showFooter = getPropValue({
    formStyles,
    item: section,
    prop: "showFormFooter",
  });

  if (showHeader === undefined) showHeader = true;
  if (showFooter === undefined) showFooter = true;

  let sectionStyle = {};
  if (section === "formHeader" && showHeader === false) {
    sectionStyle = { display: "none" };
  } else if (section === "formFooter" && showFooter === false) {
    sectionStyle = { display: "none" };
  }

  return (
    <div
      style={{
        marginBottom: "1rem",
        ...sectionStyle,
        ...readableStyles({ styles: sectionStyles["box"] }),
      }}
      className={`${section === "formHeader" ? "form-header" : "form-footer"}`}
    >
      <div
        style={{
          margin: "4px 0px",
          position: "relative",
          width: "100%",
        }}
      >
        <DisplayItems
          readableStyles={readableStyles}
          orderFormId={orderFormId}
          styles={formStyles}
          section={section}
          dataRows={section === "formHeader" ? formHeader : formFooter}
          fetchProducts={(args: any) => {
            let activePage = 1;
            if (
              typeof orderFormData.formPagination[orderFormId] !==
                "undefined" &&
              typeof orderFormData.formPagination[orderFormId][
                "active_page"
              ] !== "undefined"
            ) {
              activePage =
                orderFormData.formPagination[orderFormId]["active_page"];
            }
            fetchProducts({
              orderFormData,
              search: "",
              category: "",
              active_page: activePage,
              searching: "no",
              sort_order: "",
              show_all: false,
              attributes: { id: orderFormId },
              wholesale_role: WWOF_Frontend_Options.wholesale_role,
              per_page:
                orderFormData.formSettings[orderFormId]["products_per_page"] ||
                10,
              form_settings: orderFormData.formSettings[orderFormId],
              ...args,
            });
          }}
        />
      </div>
    </div>
  );
});

const mapStateToProps = (store: any, props: any) => ({
  orderFormData: store.orderFormData,
  formHeader: store.orderFormData.formHeader[props.orderFormId],
  formFooter: store.orderFormData.formFooter[props.orderFormId],
  formStyles: store.orderFormData.formStyles[props.orderFormId],
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      fetchProducts,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(FormHeaderFooter);
