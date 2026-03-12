import { DragDropContext, DragStart, DropResult } from "react-beautiful-dnd";
import EditorLeftContent from "./EditorLeftContent";
import EditorRightContent from "./EditorRightContent";
import "./styles.scss";
import DisplayMinReqNotice from "DisplayMinReqNotice";

// Redux
import { bindActionCreators, Dispatch } from "redux";
import { connect } from "react-redux";
import { dragAndDropActions } from "store/actions";

const {
  transferItems,
  arrangeTableColumns,
  arrangeHeaderFooterRows,
  arrangeHeaderFooterColumns,
} = dragAndDropActions;

const pulsatingEffect = (section: string, action: string) => {
  let className = "";
  if (section === "headerElements") className = "formHeaderFooterContainer";
  else if (section === "tableElements") className = "formTableContainer";

  const container: any = document.getElementsByClassName(className);

  if (action === "add" && container.length > 0) {
    Array.from(container).forEach((c: any, key: number) => {
      container[key].className += " pulsate";
    });
  } else if (action === "remove" && container.length > 0) {
    Array.from(container).forEach((c: any, key: number) => {
      container[key].className = container[key].className.replace(
        " pulsate",
        ""
      );
    });
  }
};

const EditorContent = (props: any) => {
  const { actions, data } = props;

  const onDragStart = (start: DragStart) => {
    // Show Dragging effect / Same as edit mode effect
    if (start.draggableId) {
      const draggableItems: any = document.querySelectorAll(
        ".formHeader [data-rbd-draggable-id],.formFooter [data-rbd-draggable-id]"
      );
      draggableItems.forEach((element: any) => {
        if (element.dataset.rbdDraggableId === start.draggableId) {
          element.classList.add("dragging");
        }
      });
    }

    // Hide Add New Section Button
    if (start.type === "formHeader") {
      const formHeader: any = document.querySelector(
        ".formHeader .add-new-section"
      );
      formHeader.style.opacity = "0";
    } else if (start.type === "formFooter") {
      const formFooter: any = document.querySelector(
        ".formFooter .add-new-section"
      );
      formFooter.style.opacity = "0";
    }

    pulsatingEffect(start.source.droppableId, "add");
  };

  const onDragEnd = (result: DropResult) => {
    const { source, destination } = result;

    // Remove Dragging effect / Same as edit mode effect
    if (result.draggableId) {
      const draggableItems: any = document.querySelectorAll(
        ".formHeader [data-rbd-draggable-id],.formFooter [data-rbd-draggable-id]"
      );
      draggableItems.forEach((element: any) => {
        element.classList.remove("dragging");
      });
    }

    // Show Add New Section Button
    const formHeader: any = document.querySelector(
      ".formHeader .add-new-section"
    );
    if (formHeader) formHeader.style.opacity = "1";

    const formFooter: any = document.querySelector(
      ".formFooter .add-new-section"
    );
    if (formFooter) formFooter.style.opacity = "1";

    pulsatingEffect(source.droppableId, "remove");

    if (!destination) return;

    let destElement = destination.droppableId.split("-");

    if (source.droppableId !== destination.droppableId) {
      // Drag items to the editor

      actions.transferItems({ source, destination, data, destElement });
    } else if (
      ["formHeader", "formFooter"].indexOf(destElement[0]) !== -1 &&
      destElement.length === 1
    ) {
      // Arranging Header and Footer Rows

      actions.arrangeHeaderFooterRows({
        source,
        destination,
        data,
        destElement,
      });
    } else if (["formHeader", "formFooter"].indexOf(destElement[0]) !== -1) {
      // Arranging Header and Footer Inner Columns

      actions.arrangeHeaderFooterColumns({
        source,
        destination,
        data,
        destElement,
      });
    } else if (["formTable"].indexOf(destElement[0]) !== -1) {
      // Arranging Table Columns

      actions.arrangeTableColumns({ source, destination, data, destElement });
    }
  };

  return (
    <DragDropContext onDragStart={onDragStart} onDragEnd={onDragEnd}>
      <DisplayMinReqNotice />
      <div className="parent">
        <EditorLeftContent />
        <EditorRightContent />
      </div>
    </DragDropContext>
  );
};

const mapStateToProps = (store: any, props: any) => ({
  data: store.dragAndDrop,
});

const mapDispatchToProps = (dispatch: Dispatch) => ({
  actions: bindActionCreators(
    {
      arrangeTableColumns,
      arrangeHeaderFooterRows,
      arrangeHeaderFooterColumns,
      transferItems,
    },
    dispatch
  ),
});

export default connect(mapStateToProps, mapDispatchToProps)(EditorContent);
