// DRAG AND DROP
export enum EDragAndDropActionTypes {
  RESET_DND_DATA = "RESET_DND_DATA",
  SET_DND_DATA = "SET_DND_DATA",
  ARRANGE_TABLE_COLUMNS = "ARRANGE_TABLE_COLUMNS",
  ARRANGE_HEADER_FOOTER_ROWS = "ARRANGE_HEADER_FOOTER_ROWS",
  ARRANGE_HEADER_FOOTER_COLUMNS = "ARRANGE_HEADER_FOOTER_COLUMNS",
  TRANSFER_ITEMS = "TRANSFER_ITEMS",
}

export interface IDragAndDrop {
  items: {
    // Header/Footer Elements
    "search-input": { id: string; content: string };
    "category-filter": { id: string; content: string };
    "add-selected-to-cart-button": {
      id: string;
      content: string;
    };
    "cart-subtotal": { id: string; content: string };
    "product-count": { id: string; content: string };
    pagination: { id: string; content: string };
    "search-button": { id: string; content: string };
    // Table Elements
    "product-image": { id: string; content: string };
    "product-name": { id: string; content: string };
    sku: { id: string; content: string };
    "in-stock-amount": { id: string; content: string };
    price: { id: string; content: string };
    "quantity-input": { id: string; content: string };
    "add-to-cart-button": {
      id: string;
      content: string;
    };
    "variation-dropdown": {
      id: string;
      content: string;
    };
    "short-description": {
      id: string;
      content: string;
    };
    "add-to-cart-checkbox": {
      id: string;
      content: string;
    };
    "product-meta": {
      id: string;
      content: string;
    };
  };
  formElements: {
    headerElements: {
      id: string;
      title: string;
      desc: string;
      itemIds: string[];
    };
    tableElements: {
      id: string;
      title: string;
      desc: string;
      itemIds: string[];
    };
  };
  editorArea: {
    formHeader: {
      title: string;
      rows: string[];
    };
    formTable: {
      title: string;
      itemIds: string[];
    };
    formFooter: {
      title: string;
      rows: string[];
    };
  };
}
