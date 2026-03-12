import axios from "axios";

declare var Options: any;
let headers = {};

if (typeof Options !== "undefined" && Options.nonce !== "") {
  headers = {
    "X-WP-Nonce": Options.nonce,
  };
}

export default axios.create({
  baseURL: Options.root,
  timeout: 0,
  headers: {
    ...headers,
  },
});
