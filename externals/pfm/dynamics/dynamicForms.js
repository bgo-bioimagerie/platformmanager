
/**
 * Improves forms usability
 */
export class DynamicForms {

    /**
     * Generates options whithin an html select input depending on antother html select selected option  
     * @param {string} selectorId1 id of the source select 
     * @param {string} selectorId2 id of the dependant select
     * @param {string} apiRoute
     * name of route to call the backend function returning data to fill dependant select options
     *  
     */
    dynamicSelectors(selectorId1, selectorId2, apiRoute) {
        const firstSelector = document.getElementById(selectorId1);
        firstSelector.addEventListener("change", (event) => {
            let id = event.target.value;
            const secondSelector = document.getElementById(selectorId2);
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
                    secondSelector.options.length = 0;
                    data.elements.forEach( (element, index) => {
                        secondSelector.options[index] = new Option(element.name, element.id);
                    });
                });
            });
    }

}
