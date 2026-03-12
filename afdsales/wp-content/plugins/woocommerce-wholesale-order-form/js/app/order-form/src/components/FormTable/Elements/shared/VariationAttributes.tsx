import { List } from "antd";

// This component is used when Show Variations Individually is enabled
// NOTE: variation type only show when the option above is enabled
export default function VariationAttributes(props: any) {
  const { product } = props;

  if (product.type === "variation") {
    const attributeNames = product.attributes.map((attributes: any) => {
      return `<strong>${attributes.name}</strong>:  ${attributes.option}`;
    });

    return attributeNames.length === 0 ? (
      <></>
    ) : (
      <List
        className="wwof-variation-attributes"
        size="small"
        dataSource={attributeNames}
        renderItem={(item: string) => (
          <List.Item>
            <div
              dangerouslySetInnerHTML={{
                __html: item,
              }}
            ></div>
          </List.Item>
        )}
      />
    );
  } else return <></>;
}
