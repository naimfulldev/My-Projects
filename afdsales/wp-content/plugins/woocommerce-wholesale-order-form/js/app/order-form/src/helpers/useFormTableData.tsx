import { useEffect, useState } from "react";

export default function useFormProductsData(props: any) {
  const { orderFormData, orderFormId } = props;

  const [fetching, setFetching] = useState(false);
  const [formProducts, setFormProducts] = useState([]);
  const [formStyles, setFormStyles] = useState([]);
  const [formTable, setFormTable] = useState([]);

  useEffect(() => {
    if (
      typeof orderFormData["formProducts"][orderFormId] !== "undefined" &&
      typeof orderFormData["formProducts"][orderFormId]["fetching"] !==
        "undefined"
    )
      setFetching(orderFormData["formProducts"][orderFormId]["fetching"]);

    if (
      typeof orderFormData["formProducts"][orderFormId] !== "undefined" &&
      typeof orderFormData["formProducts"][orderFormId]["products"] !==
        "undefined"
    )
      setFormProducts(orderFormData["formProducts"][orderFormId]["products"]);

    if (typeof orderFormData["formStyles"][orderFormId] !== "undefined")
      setFormStyles(orderFormData["formStyles"][orderFormId]);

    if (typeof orderFormData["formTable"][orderFormId] !== "undefined")
      setFormTable(orderFormData["formTable"][orderFormId]);
  }, [orderFormData, orderFormId]);

  return { fetching, formProducts, formStyles, formTable };
}
