
/**
 * Create an div of classes errorMessage and notUnique
 * append to the parentElement of the specified HTMLElement
 * 
 * @param {HTMLElement} element
 * 
 */
export function createErrorDiv(element) {
    let error = document.createElement("div");
    error.className = "errorMessage notUnique";
    error.style.color = "red";
    error.style.display = "none";
    element.parentElement.append(error);
}

/**
 * hide old errors, by calling hideErrors(), then displays a new error attached to a speĉific html tag
 *
 * @param {HTMLCollection} errors - previously displayed errors attached to elemHTML 
 * @param {HTMLElement} elemHTML - element to which attach the new error
 * @param msg {string} msg - message to display as an error
 * 
 */
export function displayError(errors, elemHTML, msg) {
    hideErrors(errors, elemHTML);
    let errorToShow = elemHTML.parentElement.getElementsByClassName("errorMessage")[0];
    errorToShow.innerHTML = msg;
    elemHTML.style.borderColor = "red";
    errorToShow.style.display = "block";
}

/**
 * hide old errors attached to elemHTML
 *
 * @param {HTMLCollection} errors - displayed errors attached to elemHTML 
 * @param {HTMLElement} elemHTML - element to which are attached the errors
 * 
 */
export function hideErrors(errors, elemHTML) {
    elemHTML.style.borderColor = "";
    [...errors].forEach(error => {
        error.style.display = "none";
    });
}


