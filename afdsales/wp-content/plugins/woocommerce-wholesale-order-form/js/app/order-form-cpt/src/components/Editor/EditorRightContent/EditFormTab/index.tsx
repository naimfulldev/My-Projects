import { Droppable, Draggable } from "react-beautiful-dnd";
import ElementIcon from "./ElementIcon";
import { connect } from "react-redux";

const EditFormTab = (props: any) => {
  const { data, editFormTabStrings } = props;

  const { form_elements_heading, form_elements_desc } = editFormTabStrings;

  return (
    <>
      <h3 style={{ fontWeight: "bolder" }}>{form_elements_heading}</h3>
      <p>{form_elements_desc}</p>
      <div className="form-elements">
        {Object.keys(data["formElements"]).map((columnId: any, index: any) => {
          const column = data["formElements"][columnId];

          // Future Feature, we will hide for now
          if (columnId === "wooWidgets") return <div key={index}></div>;
          else
            return (
              <div
                style={{
                  display: "flex",
                  flexWrap: "wrap",
                }}
                key={columnId}
              >
                <h4 style={{ marginTop: "10px" }}>
                  {editFormTabStrings?.[columnId] ?? column.title}
                </h4>
                <p>{column.desc}</p>
                <div
                  style={{
                    marginTop: 8,
                    width: "100%",
                  }}
                >
                  <Droppable
                    // direction="horizontal"
                    droppableId={columnId}
                    key={index}
                    type={
                      columnId !== "tableElements" ? "HEADER-FOOTER" : "DEFAULT"
                    }
                  >
                    {(provided, snapshot) => {
                      return (
                        <div
                          className={`draggable-items ${columnId}`}
                          {...provided.droppableProps}
                          ref={provided.innerRef}
                          style={{
                            display: "flex",
                            flexWrap: "wrap",
                          }}
                        >
                          {column["itemIds"].map((itemId: any, index: any) => {
                            const item = data["items"][itemId];

                            if (typeof item === "undefined")
                              return <div key={index}></div>;
                            else
                              return (
                                <Draggable
                                  key={itemId}
                                  draggableId={itemId}
                                  index={index}
                                >
                                  {(provided, snapshot) => {
                                    return (
                                      <div
                                        ref={provided.innerRef}
                                        {...provided.draggableProps}
                                        {...provided.dragHandleProps}
                                        style={{
                                          textAlign: "center",
                                          marginBottom: "10px",
                                          backgroundColor: snapshot.isDragging
                                            ? "#0071a1"
                                            : "#fff",
                                          color: snapshot.isDragging
                                            ? "#fff"
                                            : "#525252",
                                          border: "1px solid #D9D9D9",
                                          padding: "6px 16px",
                                          marginRight: "10px",
                                          borderRadius: "2px",
                                          ...provided.draggableProps.style,
                                        }}
                                      >
                                        <ElementIcon item={item} />
                                      </div>
                                    );
                                  }}
                                </Draggable>
                              );
                          })}
                          {provided.placeholder}
                        </div>
                      );
                    }}
                  </Droppable>
                </div>
              </div>
            );
        })}
      </div>
    </>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  editFormTabStrings: store.i18n.backend.edit_form_tab,
});

export default connect(mapStateToProps)(EditFormTab);
