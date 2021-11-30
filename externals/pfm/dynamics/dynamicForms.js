
/**
 * Improves forms usability
 */
export class DynamicForms {

    /**
     * Generates content of an html input depending on an html select selected option  
     * @param {string} field1 id of the source select
     * @param {string} field2 id of the dependant input
     * @param {string} apiRoute
     * name of route to call the backend function returning data to fill dependant select options
     *  
     */
    dynamicFields(field1, field2, apiRoute, spaceId) {
        apiRoute += ("/" + spaceId);
        field1.addEventListener("change", (event) => {
            let id = event.target.value;
            apiRoute += ("/" + id);
            const headers = new Headers();
                    headers.append('Content-Type','application/json');
                    headers.append('Accept', 'application/json');
            const cfg = {
                headers: headers,
                method: 'POST',
                body: null
            };
            cfg.body = JSON.stringify({
                id: id,
                id_space: spaceId
            });
            fetch(apiRoute, cfg, true).
                then((response) => response.json()).
                then(data => {
                    switch (field2.nodeName) {
                        case "SELECT":
                            let elements = Array.isArray(data.elements) ? data.elements : [data.elements];
                            field2.options.length = 0;
                            elements.forEach( (element, index) => {
                                field2.options[index] = new Option(element.name, element.id);
                            });
                            break;
                        case "#Text":
                            field2.value = data.elements;
                            break;
                        case "TEXTAREA":
                            field2.value = data.elements;
                            break;
                        default:
                            break;
                    }
                });
            });
    }

}
