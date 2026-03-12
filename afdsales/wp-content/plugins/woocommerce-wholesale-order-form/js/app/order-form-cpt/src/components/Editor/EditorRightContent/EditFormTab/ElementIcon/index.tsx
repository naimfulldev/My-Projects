import {
  SearchOutlined,
  DatabaseOutlined,
  ShoppingCartOutlined,
  ReconciliationOutlined,
  BorderlessTableOutlined,
  DownloadOutlined,
  GoldOutlined,
  FileImageOutlined,
  FileTextOutlined,
  BarcodeOutlined,
  LineChartOutlined,
  DollarCircleOutlined,
  ContainerOutlined,
  FileMarkdownOutlined,
  ClusterOutlined,
  FileSearchOutlined,
  OrderedListOutlined,
} from "@ant-design/icons";
import { connect } from "react-redux";

const DisplayElementIcon = (props: any) => {
  const { elementId } = props;

  switch (elementId) {
    // Header/Footer Elements
    case "search-input":
      return <SearchOutlined />;
    case "category-filter":
      return <DatabaseOutlined />;
    case "add-selected-to-cart-button":
      return <ShoppingCartOutlined />;
    case "cart-subtotal":
      return <ReconciliationOutlined />;
    case "product-count":
      return <BorderlessTableOutlined />;
    case "pagination":
      return <DownloadOutlined />;
    case "attribute-filter":
      return <GoldOutlined />;
    case "search-button":
      return <FileSearchOutlined />;
    // Table Elements
    case "product-image":
      return <FileImageOutlined />;
    case "product-name":
      return <FileTextOutlined />;
    case "sku":
      return <BarcodeOutlined />;
    case "in-stock-amount":
      return <LineChartOutlined />;
    case "price":
      return <DollarCircleOutlined />;
    case "quantity-input":
      return <DownloadOutlined />;
    case "add-to-cart-button":
      return <ShoppingCartOutlined />;
    case "variation-dropdown":
      return <ContainerOutlined />;
    case "standard-variation-dropdowns":
      return <ContainerOutlined />;
    case "global-attribute":
      return <ClusterOutlined />;
    case "short-description":
      return <FileTextOutlined />;
    case "add-to-cart-checkbox":
      return <OrderedListOutlined />;
    case "product-meta":
      return <FileMarkdownOutlined />;
    // WooCommerce Widgets
    case "cart-widget":
      return <ReconciliationOutlined />;
    case "filter-products-by-attribute":
      return <GoldOutlined />;
    case "filter-products-by-price":
      return <GoldOutlined />;
    default:
      return <></>;
  }
};

const ElementIcon = (props: any) => {
  const { item, dragAndDropLabels } = props;

  if (typeof item !== "undefined" && typeof item.id !== "undefined")
    return (
      <>
        <DisplayElementIcon elementId={item.id} />{" "}
        {dragAndDropLabels?.[item.id]}
      </>
    );
  else return <></>;
};

const mapStateToProps = (store: any, props: any) => ({
  dragAndDropLabels: store.i18n.backend.drag_and_drop_labels,
});

export default connect(mapStateToProps)(ElementIcon);
