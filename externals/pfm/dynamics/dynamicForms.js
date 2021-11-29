
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
    dynamicFields(field1, field2, apiRoute) {
        const firstField = document.getElementById(field1);
        firstField.addEventListener("change", (event) => {
            let id = event.target.value;
            const secondField = document.getElementById(field2);
            const spaceId = document.getElementById("id_space").value;
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
                    switch (secondField.nodeName) {
                        case "SELECT":
                            let elements = Array.isArray(data.elements) ? data.elements : [data.elements];
                            secondField.options.length = 0;
                            elements.forEach( (element, index) => {
                                secondField.options[index] = new Option(element.name, element.id);
                            });
                            break;
                        case "#Text":
                            secondField.value = data.elements;
                            break;
                        case "TEXTAREA":
                            secondField.value = data.elements;
                            break;
                        default:
                            break;
                    }
                });
            });
    }

}
