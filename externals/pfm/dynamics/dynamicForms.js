
/**
 * Improves forms usability
 */
export class DynamicForms {

    selectBuffer = {
        targetElement: []
    };

    /**
     * Generates content of one or several html inputs depending on an html select selected option  
     * @param {HTMLElement} source id of the source select
     * @param {[HTMLElements]} targets ids of the dependant inputs
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
            targets.forEach((target, index) => {
                // let targetElement = target.element;
                let id = event.target.value;
                let apiRoute = target.apiRoute;
                apiRoute += spaceId + "/" + id;

                fetch(apiRoute, cfg, true).
                    then((response) => response.json()).
                    then(data => {
                        switch (target.element.nodeName) {
                            case "SELECT":
                                /**
                                 * Since targetElement changes when cloning,
                                 * need to buffer the new one.
                                 * Indexed in case of if targets contain multiple selects
                                 **/
                                let targetElement = this.selectBuffer.targetElement[index] ?? target.element;
                                // get elements to insert as options
                                let elements = Array.isArray(data.elements) ? data.elements : [data.elements];
                                // clone target
                                let cloneElement = targetElement.cloneNode(false);
                                elements.forEach( (element) => {
                                    cloneElement.appendChild(new Option(element.name, element.id));
                                });
                                // replacing target element by its clone
                                targetElement.replaceWith(cloneElement);
                                // buffering clone element to use it as the new target element
                                this.selectBuffer.targetElement[index] = document.getElementById(targetElement.id);
                                break;
                            case "#Text":
                                target.element.value = data.elements;
                                break;
                            case "TEXTAREA":
                                target.element.value = data.elements;
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
