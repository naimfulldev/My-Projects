// STYLING
export enum EStylingActionTypes {
  SET_SHOW_STYLING = "SET_SHOW_STYLING",
  SET_STYLES = "SET_STYLES",
}
export interface IStyling {
  show: boolean;
  item: {
    type?: string;
    id?: string;
    itemId?: string;
    section?: string;
  };
  styles: Object;
}
