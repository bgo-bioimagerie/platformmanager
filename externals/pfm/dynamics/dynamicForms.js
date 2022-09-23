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
     * @param {boolean} targetingLabel if true, targets target's label, not the target itself
     */
    dynamicFields(sourceId, targets, spaceId, targetingLabel = false) {
        const headers = new Headers();
            headers.append('Content-Type','application/json');
            headers.append('Accept', 'application/json');
        const cfg = {
            headers: headers,
            method: 'POST',
            body: null
        };
        const source = document.getElementById(sourceId);
        let initialId = (source.options[source.selectedIndex] != -1) ? source.options[source.selectedIndex].value : null;
        targets.forEach(target => {
            if (target.activateOnLoad && initialId) {
                this.activateTarget(target, initialId, spaceId, cfg, targetingLabel);
            }
        });
        source.addEventListener("change", (event) => {
            const selectedId = event.target.value;
            targets.forEach((target) => {
                this.activateTarget(target, selectedId, spaceId, cfg, targetingLabel);
                });
            });
    }

    activateTarget(target, selectedId, spaceId, cfg, targetingLabel = false) {
        let targetElement = document.getElementById(target.elementId);
        let mandatory = false;
        if (targetingLabel) {
            mandatory = targetElement.hasAttribute('required');
            targetElement = this.getElementLabel(targetElement);
        }
        let apiRoute = target.apiRoute;
        apiRoute += spaceId + "/" + selectedId;
        fetch(apiRoute, cfg, true).
            then((response) => response.json()).
            then(data => {
                if (target.addEmptyItem) {
                    data.elements.unshift("");
                }
                switch (targetElement.nodeName) {
                    case "SELECT":
                        let elements = Array.isArray(data.elements) ? data.elements : [data.elements];
                        let cloneElement = targetElement.cloneNode(false);
                        elements.forEach((element) => {
                            if(element.firstname != undefined) {
                                cloneElement.appendChild(new Option(element.firstname +' ' + element.name , element.id));
                            } else {
                                cloneElement.appendChild(new Option(element.name , element.id));
                            }
                        });
                        targetElement.replaceWith(cloneElement);
                        break;
                    case "INPUT":
                        targetElement.value = data.elements;
                        break;
                    case "TEXTAREA":
                        targetElement.value = data.elements;
                        break;
                    case "LABEL":
                        if (mandatory) {
                            data.elements += '*';
                        }
                        targetElement.innerText = data.elements;
                        break;
                    default:
                        break;
                }
            }).catch( error => {
                console.error("error in setting " + targetElement.id + " data:", error);
            });
    }

    manageLineAdd(formAddName, sourceItemsName, targetItemsName, apiRoute, spaceId, targetingLabel = false) {
        const addBtn = document.getElementById(formAddName + "_add");
        addBtn.addEventListener("click", () => {
            let sourceId = this.setLastElementId(sourceItemsName);
            let targetId = this.setLastElementId(targetItemsName);
            let targets = [
                {
                    elementId: targetId,
                    apiRoute: apiRoute,
                    activateOnLoad: true
                }
            ]
            this.dynamicFields(sourceId, targets, spaceId, targetingLabel);
        });
    }

    getElementLabel(element) {
        let result = null;
        const groupElement = element.parentElement.parentElement;
        const children = [...groupElement.children];
        for (const child of children) {
            if (child.nodeName === "LABEL") {
                result = child;
                break;
            }
        }
        return result;
    }

    setLastElementId(itemsName) {
        let items = document.getElementsByName(itemsName + "[]");
        let lastIndex = items.length - 1;
        let newItem = items[lastIndex];
        newItem.id = itemsName + lastIndex.toString();
        return newItem.id;
    }

     /**
     * If two boolean input values must be related (example: if input1 == true, input2 must be equal to true)
     * functions to apply must be determinated in rules parameter
     * Rules parameter must contain 2 functions (one by input). If needed, second function can be empty
     *  
     * @param {string} firstInput id of the first input
     * @param {string} secondInput id of the second input    
     * @param {Array[function]} rules Array of functions
     * @param {boolean} formadd if true, will loop over lines for which HTMLElement name === inputname + '[]'  to apply rules on each one
     */
    linkBooleanInputs(firstInput, secondInput, rules, formadd = false) {
        const inputs1 = formadd ? document.getElementsByName(firstInput + "[]") : [document.getElementById(input1)];
        const inputs2 = formadd ? document.getElementsByName(secondInput + "[]") : [document.getElementById(input2)];
        let bothInputs = [inputs1, inputs2];

        bothInputs.forEach((inputs, i) => {
            inputs.forEach((input, j) => {
                input.addEventListener("change", () => {
                    rules[i](bothInputs[0][j], bothInputs[1][j]);
                });
            });
        });
    }

}
