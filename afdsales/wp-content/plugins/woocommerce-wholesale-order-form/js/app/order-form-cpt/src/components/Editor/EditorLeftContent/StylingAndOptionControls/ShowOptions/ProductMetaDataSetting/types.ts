import React from "react";
import { FormInstance } from "antd/lib/form";

export interface EditableRowProps {
  index: number;
}

export interface Item {
  key: React.Key;
  name: string;
  meta_key: string;
}

export interface EditableCellProps {
  title: React.ReactNode;
  editable: boolean;
  children: React.ReactNode;
  dataIndex: keyof Item;
  record: Item;
  handleSave: (record: Item) => void;
  isRequiredText: string;
}

export const EditableContext = React.createContext<FormInstance<any> | null>(
  null
);
