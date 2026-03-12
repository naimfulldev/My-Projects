import { Button, Popover } from "antd";
import { useState } from "react";

// Locations Column Data
const OrderFormLocations = (props: any) => {
  const { locations, locations_col, orderFormsTableStrings } = props;
  const [popupVisibility, setPopupVisibility] = useState(false);
  const { page, pages } = orderFormsTableStrings;

  const TotalLocations = (props: any) => {
    return (
      <>
        {locations.length > 1
          ? `${locations.length} ${pages}`
          : `${locations.length} ${page}`}
      </>
    );
  };

  const LocationList = () => {
    return (
      <ul style={{ listStyleType: "none", padding: "0px", margin: "0px" }}>
        {locations.map((location: any, key: any) => {
          return (
            <li key={key}>
              <Button
                size="large"
                style={{ padding: "0px" }}
                key={key}
                href={location.permalink}
                target="_blank"
                type="link"
              >
                {location.post_title}
              </Button>
            </li>
          );
        })}
      </ul>
    );
  };

  if (locations.length > 0)
    return (
      <Popover
        content={LocationList}
        title={locations_col}
        trigger="click"
        visible={popupVisibility}
        onVisibleChange={(visible) => setPopupVisibility(visible)}
      >
        <Button size="large" type="link">
          <TotalLocations />
        </Button>
      </Popover>
    );
  else return <></>;
};

export default OrderFormLocations;
