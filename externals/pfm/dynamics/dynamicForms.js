
/**
 * Improves forms usability
 */
export class DynamicForms {

    /**
     * @typedef {Object} Target
     * @property {string} elementId id of a dependant input
     * @property {string} apiRoute name of route to call the backend function returning data
     */

    /**
     * Generates content of one or several html inputs depending on an html select selected option
     * 
     * @param {string} source id of the source select
     * @param {Target} targets ids of the dependant inputs     
     * @param {string} spaceId
     */
    dynamicFields(sourceId, targets, spaceId) {
        const headers = new Headers();
            headers.append('Content-Type','application/json');
            headers.append('Accept', 'application/json');
        const cfg = {
            headers: headers,
            method: 'POST',
            body: null
        };
        const source = document.getElementById(sourceId);
        source.addEventListener("change", (event) => {
            targets.forEach((target) => {
                let targetElement = document.getElementById(target.elementId);
                let id = event.target.value;
                let apiRoute = target.apiRoute;
                apiRoute += spaceId + "/" + id;

                fetch(apiRoute, cfg, true).
                    then((response) => response.json()).
                    then(data => {
                        switch (targetElement.nodeName) {
                            case "SELECT":
                                // get elements to insert as options
                                let elements = Array.isArray(data.elements) ? data.elements : [data.elements];
                                // clone target
                                let cloneElement = targetElement.cloneNode(false);
                                elements.forEach((element) => {
                                    cloneElement.appendChild(new Option(element.name, element.id));
                                });
                                // replacing target element by its clone
                                targetElement.replaceWith(cloneElement);
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
                    }).catch( error => {
                        console.error("error in setting " + targetElement.id + " data:", error);
                    });
                });
            });
    }

}
