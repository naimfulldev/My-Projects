import ReactDOM from "react-dom";
import App from "./App";

// Store
import store from "./store";
import { Provider } from "react-redux";

// Find all DOM containers, and render order form into them.
document.querySelectorAll(".order-form").forEach((domContainer: any) => {
  ReactDOM.render(
    <Provider store={store}>
      <App
        attributes={JSON.parse(
          domContainer.attributes["data-order-form-attr"].value
        )}
      />
    </Provider>,
    domContainer
  );
});
