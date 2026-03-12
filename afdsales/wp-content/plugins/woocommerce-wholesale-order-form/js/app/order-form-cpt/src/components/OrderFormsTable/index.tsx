import { useEffect, useState } from "react";
import {
  Table,
  Tag,
  Tooltip,
  Input,
  notification,
  Button,
  Empty,
  Modal,
} from "antd";
import { Link, useLocation } from "react-router-dom";
import OrderFormLocations from "./OrderFormLocations";

import copy from "copy-to-clipboard";
import {
  CopyOutlined,
  FormOutlined,
  DeleteOutlined,
  ExclamationCircleOutlined,
  QuestionCircleOutlined,
} from "@ant-design/icons";

import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";

// Actions
import { orderFormActions } from "store/actions";
const { fetchOrderForms, deleteOrderForm, setPage } = orderFormActions;

const { confirm } = Modal;

const OrderFormsTable = (props: any) => {
  const { orderFormsTableStrings } = props;

  // Strings from backend
  const {
    name_col,
    shortcode_col,
    locations_col,
    status_col,
    action_col,
    locations_tooltip,
    edit_str,
    delete_str,
    confirm_delete_msg,
    confirm_delete_msg_plural,
    draft_str,
    published_str,
    copy_str,
    copied_str,
    delete_success,
    delete_failed,
    ok_text,
    cancel_text,
    of_text,
    items_text,
  } = orderFormsTableStrings;

  const [toolTip, setToolTip] = useState(copy_str);
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);

  const pathName = useLocation().pathname;
  const params = useLocation().search;
  const siteURL = params.length > 0 ? `${pathName}${params}&` : `${pathName}?`;

  const { orderForm, actions } = props;
  const { data } = orderForm;
  const { pagination } = orderForm;

  const copyToClipboard = (value: string) => {
    copy(value);
    setToolTip(copied_str);
  };

  const dataSource =
    data.length > 0
      ? data.map((data: any) => {
          return {
            key: data.id,
            name: data.title,
            shortcode: (
              <Input
                className="order-form-field textbox shortcode"
                size="large"
                disabled={false}
                addonAfter={
                  <Tooltip title={toolTip}>
                    <div
                      style={{
                        width: "34px",
                        height: "34px",
                        cursor: "pointer",
                      }}
                      onMouseLeave={() =>
                        setTimeout(() => setToolTip(copy_str), 200)
                      }
                      onClick={() => copyToClipboard(data.content)}
                    >
                      <CopyOutlined />
                    </div>
                  </Tooltip>
                }
                value={data.content}
              />
            ),
            locations: (
              <OrderFormLocations
                locations={data.locations}
                locations_col={locations_col}
                orderFormsTableStrings={orderFormsTableStrings}
              />
            ),
            status:
              data.status === "draft" ? (
                <Tag color="#aaaaaa">{draft_str}</Tag>
              ) : (
                <Tag color="#91c67f">{published_str}</Tag>
              ),
          };
        })
      : [];

  const confirmDelete = (items: any) => {
    confirm({
      title: `${
        Array.isArray(items) && items.length > 1
          ? confirm_delete_msg_plural
          : confirm_delete_msg
      }`,
      icon: <ExclamationCircleOutlined />,
      centered: true,
      onOk() {
        actions.deleteOrderForm({
          post_id: items,
          pagination,
          successCB: () => {
            notification["success"]({
              message: delete_success,
            });

            setSelectedRowKeys([]);
          },
          failCB: () =>
            notification["error"]({
              message: delete_failed,
            }),
        });
      },
      okText: ok_text ?? "OK",
      cancelText: cancel_text ?? "Cancel",
    });
  };

  const columns = [
    {
      title: name_col,
      dataIndex: "name",
      key: "name",
      className: "name",
      render: (text: any, record: any, index: any) => (
        <Link
          to={`${siteURL}sub-page=edit&post=${record.key}`}
          style={{ marginRight: 16 }}
        >
          {text}
        </Link>
      ),
    },
    {
      title: shortcode_col,
      dataIndex: "shortcode",
      key: "shortcode",
    },
    {
      title: () => (
        <>
          {`${locations_col} `}
          <Tooltip title={locations_tooltip}>
            <QuestionCircleOutlined />
          </Tooltip>
        </>
      ),
      dataIndex: "locations",
      key: "locations",
      className: "locations",
    },
    {
      title: status_col,
      dataIndex: "status",
      key: "status",
    },
    {
      title: action_col,
      dataIndex: "",
      key: "x",
      render: (data: any) => {
        return (
          <>
            <Link
              to={`${siteURL}sub-page=edit&post=${data.key}`}
              style={{ marginRight: 16 }}
            >
              <Tooltip title={edit_str}>
                <FormOutlined style={{ color: "#0071a1" }} />
              </Tooltip>
            </Link>

            <Tooltip title={delete_str}>
              <DeleteOutlined
                onClick={() => confirmDelete(data.key)}
                style={{ color: "#FF0000" }}
              />
            </Tooltip>
          </>
        );
      },
    },
  ];

  const rowSelection = {
    selectedRowKeys,
    onChange: (selectedRowKeys: any) => {
      setSelectedRowKeys(selectedRowKeys);
    },
  };

  useEffect(() => {
    actions.fetchOrderForms({ page: pagination.page });
  }, []);

  return (
    <>
      <div style={{ marginBottom: "8px", height: "45px" }}>
        <Button
          size="large"
          danger
          style={{ display: selectedRowKeys.length === 0 ? "none" : "block" }}
          onClick={() => confirmDelete(selectedRowKeys)}
        >
          {orderFormsTableStrings.delete_str}
        </Button>
      </div>
      <Table
        className="order-forms"
        rowSelection={rowSelection}
        dataSource={dataSource}
        columns={columns}
        loading={orderForm.loading}
        bordered={true}
        pagination={{
          position: ["bottomCenter"],
          current: pagination.page,
          total: pagination.total,
          pageSize: pagination.pageSize,
          showTotal: (total: number, range: any) =>
            `${range[0]} - ${range[1]} ${of_text} ${total} ${items_text}`,
          onChange: (page: number) => {
            actions.setPage({ page });
            actions.fetchOrderForms({ page });
          },
          pageSizeOptions: [],
        }}
        locale={{
          emptyText: <Empty description={false} />,
        }}
      />
      <div style={{ marginTop: "-55px", height: "45px" }}>
        <Button
          size="large"
          danger
          style={{ display: selectedRowKeys.length === 0 ? "none" : "block" }}
          onClick={() => confirmDelete(selectedRowKeys)}
        >
          {orderFormsTableStrings.delete_str}
        </Button>
      </div>
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  orderForm: store.orderForm,
  orderFormsTableStrings: store.i18n.backend.order_forms_table,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      fetchOrderForms,
      deleteOrderForm,
      setPage,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(OrderFormsTable);
