import { EMinRequirementsTypes } from "types/index";

export const minRequirements = {
  wwppRequiredVersions: (payload: any) => ({
    type: EMinRequirementsTypes.WWPP_REQUIRED_VERSIONS,
    payload,
  }),
  removeMinimumWWPPFailMessage: (payload: any) => ({
    type: EMinRequirementsTypes.REMOVE_WWPP_MIN_FAIL_MESSAGE,
    payload,
  }),
};
