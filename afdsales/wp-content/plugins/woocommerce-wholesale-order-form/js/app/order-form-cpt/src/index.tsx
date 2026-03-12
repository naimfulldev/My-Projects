import ReactDOM from "react-dom";
import App from "./App";
import { BrowserRouter } from "react-router-dom";

// Store
import store from "./store";
import { Provider } from "react-redux";

import "./styles.scss";

ReactDOM.render(
  <Provider store={store}>
    <BrowserRouter>
      <App />
    </BrowserRouter>
  </Provider>,
  document.getElementById("wwof-order-forms-admin")
);
