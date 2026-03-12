export const updateStyling = (props: any) => {

    const { setStyles, styling, id, target, toUpdate } = props;

    if (typeof styling.styles[id] === 'undefined') {
        const newData = {
            [id]: {
                [target]: {
                    ...toUpdate
                }
            }
        }

        setStyles({
            ...styling,
            styles: {
                ...styling.styles,
                ...newData
            }
        });

    } else {

        setStyles({
            ...styling,
            styles: {
                ...styling.styles,
                [id]: {
                    ...styling.styles[id],
                    [target]: {
                        ...styling.styles[id][target],
                        ...toUpdate
                    }
                }
            }
        });

    }

}