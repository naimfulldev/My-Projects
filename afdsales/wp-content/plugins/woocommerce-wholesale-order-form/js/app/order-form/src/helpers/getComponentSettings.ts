/*
 * The purpose of these helper is to get all required option set per component that is needed for fetching products.
 * The form settings is stored in the db that contains required option but some order form doesnt have the save data yet in the db.
 * This is why get the option data in the component then store to redux state.
 * Incase the variation selector is not yet in db then we pull the data in the component option.
 * For now this only returns variation style, can be updated to return more data later.
 *
 * @since 1.19
 */

import { getPropValue } from "./getPropValue";

export const getComponentSettings = (props: any) => {
  const { formStyles } = props;

  const variationSelector =
    getPropValue({
      formStyles,
      item: "variation-dropdown",
      prop: "variationSelectorStyle",
    }) || "combo";

  return {
    variation_selector_style: variationSelector,
  };
};
