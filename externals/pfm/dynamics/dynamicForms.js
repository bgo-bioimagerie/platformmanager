
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
    dynamicFields(source, targets, spaceId) {
        const headers = new Headers();
            headers.append('Content-Type','application/json');
            headers.append('Accept', 'application/json');
        const cfg = {
            headers: headers,
            method: 'POST',
            body: null
        };
        source.addEventListener("change", (event) => {
            targets.forEach(target => {
                let targetElement = target.element;
                let apiRoute = target.apiRoute;
                apiRoute += spaceId;
                let id = event.target.value;
                apiRoute += ("/" + id);
                fetch(apiRoute, cfg, true).
                    then((response) => response.json()).
                    then(data => {
                        switch (targetElement.nodeName) {
                            case "SELECT":
                                let elements = Array.isArray(data.elements) ? data.elements : [data.elements];
                                targetElement.options.length = 0;
                                elements.forEach( (element, index) => {
                                    targetElement.options[index] = new Option(element.name, element.id);
                                });
                                break;
                            case "#Text":
                                targetElement.value = data.elements;
                                break;
                            case "TEXTAREA":
                                targetElement.value = data.elements;
                                break;
                            default:
                                break;
                        }
                    });
                });
            });
    }

}
