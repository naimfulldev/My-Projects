import React, { useEffect, useState } from "react";
import { Button, Table, Popconfirm, Tooltip, Form } from "antd";
import { DeleteOutlined, DragOutlined } from "@ant-design/icons";
import { EditableContext, EditableRowProps, Item } from "./types";
import EditableCell from "./EditableCell";
import generateId from "helpers/generateId";
import {
  SortableContainer,
  SortableElement,
  SortableHandle,
} from "react-sortable-hoc";
import { arrayMoveImmutable } from "array-move";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { orderFormActions } from "store/actions";

import "./style.scss";

const { setOrderFormSettingsData } = orderFormActions;

const DragHandle = SortableHandle(() => (
  <DragOutlined className="drag-handle" />
));

const SortableItem = SortableElement((props: any) => <tr {...props} />);
const SortableContainer2 = SortableContainer((props: any) => (
  <tbody {...props} />
));

const ProductMetaDataSetting = (props: any) => {
  const {
    styling,
    setStyles,
    id,
    target,
    updateStyling,
    getPropValue,
    actions,
    productMetaStrings,
  } = props;

  const {
    label,
    description,
    click_update,
    add_row,
    the_name,
    sure_to_delete,
    ok_text,
    cancel_text,
  } = productMetaStrings;

  const [count, setCount] = useState<number>(0);
  const [dataSource, setDataSource] = useState<Item[]>([]);
  const [dataSourceFromDB, setDataSourceFromDB] = useState<Item[]>(
    getPropValue({
      styling,
      id,
      target,
      style: "metaData",
      extra: "",
    }) || []
  );
  const { setOrderFormSettingsData } = actions;

  // Something is changed flag
  useEffect(() => {
    if (styling?.show === true) {
      const metaData =
        getPropValue({
          styling,
          id,
          target,
          style: "metaData",
          extra: "",
        }) || [];
      setDataSourceFromDB(metaData);
      setCount(metaData.length);
    }
  }, [styling?.show]);

  // Load data source from db
  useEffect(() => {
    setDataSource(
      getPropValue({
        styling,
        id,
        target,
        style: "metaData",
        extra: "",
      }) || []
    );
  }, [id]);

  // Update redux state
  useEffect(() => {
    updateStyling({
      setStyles,
      styling,
      id,
      target,
      toUpdate: {
        metaData: dataSource,
      },
    });

    setOrderFormSettingsData({
      meta_data: dataSource,
    });
  }, [dataSource]);

  const columns = [
    {
      title: productMetaStrings?.name,
      dataIndex: "name",
      editable: true,
      render: (data: string, record: { key: React.Key }) => {
        if (data.length > 12) {
          return (
            <Tooltip title={data}>{`${data.substring(0, 10)}...`}</Tooltip>
          );
        }
        return data;
      },
    },
    {
      title: productMetaStrings?.meta_key,
      dataIndex: "meta_key",
      editable: true,
      render: (data: string, record: { key: React.Key }) => {
        if (data.length > 12) {
          return (
            <Tooltip title={data}>{`${data.substring(0, 10)}...`}</Tooltip>
          );
        }
        return data;
      },
    },
    {
      width: 50,
      title: productMetaStrings?.action,
      dataIndex: "operation",
      render: (data: string, record: { key: React.Key }, index: number) =>
        dataSource.length >= 1 ? (
          <>
            <Popconfirm
              placement="left"
              title={sure_to_delete}
              onConfirm={() => handleDelete(record.key)}
              okText={ok_text ?? "Ok"}
              cancelText={cancel_text ?? "Cancel"}
            >
              <DeleteOutlined style={{ color: "#FF0000" }} />
            </Popconfirm>
            &nbsp;
            <DragHandle />
          </>
        ) : null,
    },
  ];

  // Adds new row
  const handleAdd = () => {
    const newData: Item = {
      key: generateId() + `_${count}`,
      name: the_name,
      meta_key: productMetaStrings?.meta_key2,
    };
    setCount(count + 1);
    setDataSource([...dataSource, newData]);
  };

  // Save when editing
  const handleSave = (row: Item) => {
    const newData = [...dataSource];
    const index = newData.findIndex((item) => row.key === item.key);
    const item = newData[index];
    newData.splice(index, 1, {
      ...item,
      ...row,
    });
    setDataSource(newData);
  };

  // Remove row
  const handleDelete = (key: React.Key) => {
    setCount(count - 1);
    setDataSource(dataSource.filter((item) => item.key !== key));
  };

  const columnsData = columns.map((col) => {
    if (!col.editable) {
      return col;
    }
    return {
      ...col,
      onCell: (record: Item) => ({
        record,
        editable: col.editable,
        dataIndex: col.dataIndex,
        title: col.title,
        handleSave,
        isRequiredText: productMetaStrings?.is_required,
      }),
    };
  });

  const DraggableContainer = (props: any) => (
    <SortableContainer2
      useDragHandle
      disableAutoscroll
      helperClass="row-dragging"
      onSortEnd={onSortEnd}
      {...props}
    />
  );

  const DraggableBodyRow = ({ className, style, ...restProps }: any) => {
    // function findIndex base on Table rowKey props and should always be a right array index
    let index = dataSource.findIndex(
      (x) => x.key === restProps["data-row-key"]
    );
    if (index < 0) {
      index = 0;
    }
    return <SortableItem index={index} {...restProps} />;
  };

  const EditableRow: React.FC<EditableRowProps> = ({ index, ...props }) => {
    const [form] = Form.useForm();

    return (
      <Form form={form} component={false}>
        <EditableContext.Provider value={form}>
          <DraggableBodyRow {...props} />
        </EditableContext.Provider>
      </Form>
    );
  };

  const onSortEnd = ({ oldIndex, newIndex }: any) => {
    const cData: any = dataSource;
    if (oldIndex !== newIndex) {
      const newData = arrayMoveImmutable(
        [].concat(cData),
        oldIndex,
        newIndex
      ).filter((el) => !!el);
      setDataSource(newData);
    }
  };

  const components = {
    body: {
      wrapper: DraggableContainer,
      row: EditableRow,
      cell: EditableCell,
    },
  };

  return (
    <div className="product-meta-setting">
      <label htmlFor="product-meta-setting">{label}:</label>
      <span>{description}</span>
      <Table
        components={components}
        rowClassName={() => "editable-row"}
        bordered
        dataSource={dataSource}
        columns={columnsData}
        pagination={false}
      />
      <div className="product-meta-actions">
        <div>
          {JSON.stringify(dataSourceFromDB) !== JSON.stringify(dataSource) ? (
            <p className="update-msg">
              <i>{click_update}</i>
            </p>
          ) : (
            ""
          )}
        </div>
        <div>
          <Button
            className="add-row"
            onClick={handleAdd}
            type="primary"
            disabled={count === 5 ? true : false}
          >
            {add_row}
          </Button>
        </div>
      </div>
    </div>
  );
};

const mapStateToProps = (store: any) => ({
  productMetaStrings:
    store.i18n.backend.styling_and_option_controls.show_options.product_meta,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators({ setOrderFormSettingsData }, dispatch),
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(ProductMetaDataSetting);
