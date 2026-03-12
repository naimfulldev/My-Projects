export const findItemColumnId = (dragAndDrop: any, itemId: string) => {

    for (let section of Object.keys(dragAndDrop['editorArea'])) {
        if (['formHeader', 'formFooter'].includes(section)) {
            let rows = dragAndDrop['editorArea'][section]['rows'];
            if (rows.length > 0) {
                for (let i = 0; i < rows.length; i++) {
                    let columns = rows[i]['columns'];
                    if (columns.length > 0) {
                        for (let j = 0; j < columns.length; j++) {
                            let itemIds = columns[j]['itemIds'];
                            if (itemIds.length > 0) {
                                const index = itemIds.indexOf(itemId);
                                if (index >= 0) return columns[j]['colId'];
                            }
                        }
                    }
                }
            }
        }
    }

    return false;

}