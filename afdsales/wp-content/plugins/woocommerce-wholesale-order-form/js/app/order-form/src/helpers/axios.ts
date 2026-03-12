import axios from "axios";

declare var WWOF_Frontend_Options: any;
let headers = {};

if (
  typeof WWOF_Frontend_Options !== "undefined" &&
  WWOF_Frontend_Options.nonce !== ""
) {
  headers = {
    "X-WP-Nonce": WWOF_Frontend_Options.nonce,
  };
}

export default axios.create({
  baseURL: WWOF_Frontend_Options.root,
  timeout: 0,
  headers: {
    ...headers,
  },
});
