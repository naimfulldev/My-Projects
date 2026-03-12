import { useState, useEffect, useRef } from "react";
import {
  Form,
  Input,
  Button,
  notification,
  PageHeader,
  message,
  Tooltip,
  Modal,
} from "antd";
import { Redirect } from "react-router-dom";
import OrderFormContent from "./OrderFormContent";
import StylingAndOptionControls from "./StylingAndOptionControls";

import copy from "copy-to-clipboard";
import { CopyOutlined, ExclamationCircleOutlined } from "@ant-design/icons";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import {
  orderFormActions,
  stylingActions,
  dragAndDropActions,
  productActions,
} from "store/actions";

// Helpers
import { usePageInfo } from "helpers/usePageInfo";
import NotFound from "./NotFound";

const { confirm } = Modal;

const { fetchProducts } = productActions;
const { setDndData, resetDndData } = dragAndDropActions;
const { setStyles, setShowStyling } = stylingActions;
const {
  addNewOrderForm,
  editOrderForm,
  fetchOrderForm,
  setOrderFormSettingsData,
  deleteOrderForm,
  resetOrderFormData,
} = orderFormActions;

interface IOrderFormData {
  id: number;
  status: string;
  type: string;
  title: string;
  content: string;
}

const EditorLeftContent = (props: any) => {
  const {
    styling,
    actions,
    orderForm,
    orderFormsTableStrings,
    editorLeftContentStrings,
  } = props;

  const {
    cant_fetch,
    order_form_added,
    error_adding,
    order_form_saved_draft,
    order_form_saved,
    error_updating,
    add_order_form,
    edit_order_form,
    save_draft,
    publish_str,
    update_str,
    delete_form,
    form_title,
    is_required,
  } = editorLeftContentStrings;

  const {
    confirm_delete_msg,
    copy_str,
    copied_str,
    delete_success,
    delete_failed,
  } = orderFormsTableStrings;

  const { pagination } = orderForm;

  const [fetching, setFetching] = useState(true);

  const [deleted, setDeleted] = useState(false);
  const [toolTip, setToolTip] = useState(copy_str);
  const [shortcode, setShortcode] = useState("");
  const [orderFormID, setOrderFormID] = useState(null);
  const [orderFormStatus, setOrderFormStatus] = useState("");
  const [orderFormData, setOrderFormData] = useState<IOrderFormData>({
    id: 0,
    status: "",
    type: "",
    title: "",
    content: "",
  });

  const { pageType, editPath, postID, pathName } = usePageInfo(orderFormID);

  const [form] = Form.useForm();
  const formEl = useRef(form);
  const validateMessages = {
    // eslint-disable-next-line no-template-curly-in-string
    required: "${label} " + is_required,
  };

  useEffect(() => {
    try {
      if (postID === 0 || Object.entries(orderForm.settingsData).length > 0) {
        actions.fetchProducts({
          per_page: orderForm.settingsData.products_per_page,
          sort_order: orderForm.settingsData.sort_order || "desc",
          sort_by: orderForm.settingsData.sort_by || "date",
          category: orderForm.selectedCategory || "",
          form_settings: {
            tax_display: orderForm.taxDisplay,
            selected_category: orderForm.selectedCategory,
            filtered_categories: orderForm.filteredCategories,
            excluded_categories: orderForm.excludedCategories,
            ...orderForm.settingsData,
          },
        });
      }
    } catch (e) {
      console.log(e);
    }
  }, [orderForm.settingsData]);

  useEffect(() => {
    if (pageType === "edit" && postID > 0) {
      actions.fetchOrderForm({
        id: postID,
        successCB: (data: any) => {
          setOrderFormStatus(data.status);

          form.setFieldsValue({
            title: data.title,
          });

          setOrderFormData(data);
          setShortcode(data.content);

          actions.setDndData({
            ...props.data,
            formElements: {
              ...props.data.formElements,
              ...data.meta.form_elements,
            },
            editorArea: {
              ...props.data.editorArea,
              ...data.meta.editor_area,
            },
          });

          actions.setStyles({
            ...styling,
            styles: {
              ...styling.styles,
              ...data.meta.styles,
            },
          });

          // Order Form Settings
          actions.setOrderFormSettingsData(data.meta.settings);
          setFetching(false);
        },
        failCB: () => {
          setFetching(false);
          notification["error"]({
            message: cant_fetch,
          });
        },
      });
    } else {
      // Order Form Settings
      actions.setOrderFormSettingsData({});
    }
  }, [postID]);

  const onFinish = (data: any) => {
    data.form_elements = props.data.formElements;
    data.editor_area = props.data.editorArea;
    data.styles = styling.styles;
    data.settings = props.orderForm.settingsData;
    data.status = orderFormStatus;
    data.settings.selected_category = props.orderForm.selectedCategory;
    data.settings.filtered_categories = props.orderForm.filteredCategories;
    data.settings.tax_display = props.orderForm.taxDisplay;
    data.settings.excluded_categories = props.orderForm.excludedCategories;
    data.settings.subtotal_pretext = props.orderForm.subtotalPreText;
    data.settings.subtotal_suffix = props.orderForm.subtotalSuffix;
    data.settings.quantity_restriction = props.orderForm.quantityRestriction;

    if (pageType === "add-new") {
      actions.addNewOrderForm({
        data,
        successCB: (data: any) => {
          message.success(order_form_added);
          setOrderFormID(data.id);
        },
        failCB: () => {
          message.error(error_adding);
        },
      });
    } else if (pageType === "edit" && orderFormData.id > 0) {
      data.id = orderFormData.id;

      actions.editOrderForm({
        data,
        successCB: () => {
          setOrderFormData({
            ...orderFormData,
            status: orderFormStatus,
          });

          if (orderFormStatus === "draft")
            message.success(order_form_saved_draft);
          else message.success(order_form_saved);
        },
        failCB: () => {
          message.error(error_updating);
        },
      });
    }
  };

  const copyToClipboard = (value: string) => {
    copy(value);
    setToolTip(copied_str);
  };

  const confirmDelete = (post_id: any) => {
    actions.setShowStyling({ show: false });

    confirm({
      title: confirm_delete_msg,
      icon: <ExclamationCircleOutlined />,
      centered: true,
      onOk() {
        actions.deleteOrderForm({
          post_id,
          pagination,
          successCB: () => {
            notification["success"]({
              message: delete_success,
            });
            setDeleted(true);

            // Reset dragged items in the editor once the form is deleted
            actions.resetDndData();
          },
          failCB: () =>
            notification["error"]({
              message: delete_failed,
            }),
        });
      },
      onCancel() {},
      okText: orderFormsTableStrings?.ok_text ?? "OK",
      cancelText: orderFormsTableStrings?.cancel_text ?? "Cancel",
    });
  };

  if (deleted) return <Redirect to={`${pathName}?page=order-forms`} />;

  return orderFormID === null ? (
    <div className="left-content">
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "1fr 350px",
        }}
      >
        <PageHeader
          onBack={() => {
            localStorage.removeItem("rowsHeight"); // Table Rows Height
            actions.setShowStyling({ show: false });
            actions.setStyles({ styles: {} });
            actions.resetDndData();
            actions.resetOrderFormData();
            window.history.back();
          }}
          className="page-header"
          title={pageType === "add-new" ? add_order_form : edit_order_form}
          subTitle={
            fetching === false && orderFormData.id === 0 ? (
              <></>
            ) : (
              <>
                {pageType === "add-new" ||
                (pageType === "edit" && orderFormStatus === "draft") ? (
                  <Button
                    onClick={() => {
                      actions.setShowStyling({ show: false });
                      formEl.current.submit();
                      setOrderFormStatus("draft");
                    }}
                    style={{
                      background: "#f3f5f6",
                      color: "#0071a1",
                      border: "1px solid #0071a1",
                      fontWeight: "bold",
                      marginRight: "10px",
                    }}
                  >
                    {save_draft}
                  </Button>
                ) : (
                  ""
                )}
                <Button
                  onClick={() => {
                    actions.setShowStyling({ show: false });
                    setOrderFormStatus("publish");
                    formEl.current.submit();
                  }}
                  style={{
                    background: "#f3f5f6",
                    color: "#0071a1",
                    border: "1px solid #0071a1",
                    fontWeight: "bold",
                  }}
                >
                  {pageType === "add-new" || orderFormData.status === "draft"
                    ? publish_str
                    : update_str}
                </Button>
                {pageType === "edit" ? (
                  <Button
                    danger
                    type="link"
                    onClick={() => confirmDelete(postID)}
                  >
                    {delete_form}
                  </Button>
                ) : (
                  ""
                )}
              </>
            )
          }
        />
        <Input
          size="large"
          style={{ display: shortcode ? "block" : "none" }}
          disabled={false}
          addonAfter={
            <Tooltip title={toolTip}>
              <div
                style={{
                  width: "38px",
                  height: "38px",
                  cursor: "pointer",
                }}
                onMouseLeave={() => setTimeout(() => setToolTip(copy_str), 200)}
                onClick={() => copyToClipboard(shortcode)}
              >
                <CopyOutlined />
              </div>
            </Tooltip>
          }
          value={shortcode || '[wwof_product_listing beta="true"]'}
          className="order-form-field textbox shortcode"
        />
      </div>

      <Form
        id="order-form"
        ref={formEl}
        style={{ marginTop: "20px" }}
        form={form}
        name="nest-messages"
        onFinish={onFinish}
        validateMessages={validateMessages}
      >
        <Form.Item
          label={form_title}
          name="title"
          rules={[{ required: true }]}
          style={{ margin: "10px" }}
        >
          <Input
            placeholder={form_title}
            className="order-form-field textbox title"
          />
        </Form.Item>
        <Form.Item>
          {fetching === false && orderFormData.id === 0 ? (
            <NotFound />
          ) : (
            <>
              <OrderFormContent />
              <StylingAndOptionControls />
            </>
          )}
        </Form.Item>
      </Form>
    </div>
  ) : (
    <Redirect to={editPath} />
  );
};

const mapStateToProps = (store: any, props: any) => ({
  data: store.dragAndDrop,
  orderForm: store.orderForm,
  styling: store.styling,
  orderFormsTableStrings: store.i18n.backend.order_forms_table,
  editorLeftContentStrings: store.i18n.backend.editor_left_content,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      addNewOrderForm,
      editOrderForm,
      fetchOrderForm,
      setStyles,
      setDndData,
      resetDndData,
      setOrderFormSettingsData,
      fetchProducts,
      deleteOrderForm,
      setShowStyling,
      resetOrderFormData,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(EditorLeftContent);
