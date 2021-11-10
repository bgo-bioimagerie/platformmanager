<script type="text/javascript">

// get const values userId, save btn and form elements
if (document.getElementById("id")) {
   userId = document.getElementById("id").value;
} else {
    userId = 0;
}

form = document.getElementById("editForm")
    || document.getElementById("createuseraccountform")
    || document.getElementById("createaccountform")
    || document.getElementById("usermyaccountedit");
    
saveBtn = document.getElementById("editFormsubmit");
inputs = form.getElementsByTagName("input");
isPwd = [...inputs].filter(input => input.id === "pwd").length > 0;
pwdInput = "";
pwdConfirmInput = "";
// check elements when submitting form
form.setAttribute("onsubmit", "return validateUserForm(event)");


// prepare unique elements checking
uniqueElemsHTML = document.getElementsByClassName("unique");
uniqueElems = [...uniqueElemsHTML];
uniqueElems.forEach((elem) => {
    let elemId = elem.id; // why passing elem.id directly returns another value ??? Does it ?
    // check elem value when focusing out
    elem.setAttribute("onblur", "validateUserForm(" + elemId + ")");
    // create error div to display warnings to the user. Hide it
    createErrorDiv(elem);
});

// prepare password checking
if (isPwd) {
    pwdInput = document.getElementById("pwd");
    pwdConfirmInput = document.getElementById("pwdconfirm");
    pwdConfirmInput.setAttribute("onblur", "validatePasswords()");
    createErrorDiv(pwdConfirmInput);
}

/**
 * Create an div of classes errorMessage and notUnique
 * append to the parentElement of the specified HTMLElement
 * 
 * @param {HTMLElement} element
 * 
 */
function createErrorDiv(element) {
    let error = document.createElement("div");
    error.className = "errorMessage notUnique";
    error.style.color = "red";
    error.style.display = "none";
    element.parentElement.append(error);
}


/**
 * If called at form submission, prevents submission, check form values, then, if valid, submit form.
 * 
 * What does it test ? 
 * - values of html elements of class "unique"
 * - email format (regexp)
 * - passwords matching (when password confirmation field)
 * 
 * When called in a form, check input fileds of class "unique".
 * Setting checkUnicity param to true in Form::addEmail() or Form::addText() will lead to use this control function.
 * Forbid form submission and displays warns to user if a "unique" input field is filled with data already existing in database.
 * 
 * @param {string} origin
 * 
 */
function validateUserForm(origin) {
    let unicity = true;
    let pwdIdentical = true;
    let msg;
    let submissionRequest = !(!origin.target);
    let elemsToCheck = [...uniqueElems];
    if (!submissionRequest) {
        // not an onsubmit event
        elemsToCheck.length = 0;
        elemsToCheck.push(origin);
    } else {
        origin.preventDefault();
        // check if passwords match
        pwdIdentical = isPwd ? validatePasswords() : true;
    }

    const headers = new Headers();
            headers.append('Content-Type','application/json');
            headers.append('Accept', 'application/json');
    const cfg = {
        headers: headers,
        method: 'POST',
        body: null
    };
    
    // for each element fetch unicity info then display or delete warnings
    elemsToCheck.forEach((elem, index) => {
        const elemHTML = document.getElementById(elem.id);
        const value = elemHTML.value;
        const type = elemHTML.id;
        cfg.body = JSON.stringify({
            type: type,
            value: value,
            user: this.userId
        });
        let errors = elemHTML.parentElement.getElementsByClassName("errorMessage");
        // Do we still need errordISPLAYED ?
        let errorDisplayed = (errors.length > 0);
        let invalidEmail = (type === "email") && !validateEmail(value);
        fetch(`coreaccountisunique`, cfg, true).
            then((response) => response.json()).
            then(data => {
                if (!data.isUnique || invalidEmail) {
                    // Data is not valid
                    unicity = false;
                    if (!data.isUnique) {
                        // TODO: find a way to get values from CoreTranslator !!!
                        msg = "this " + type + " already exists";
                        displayError(errors, elemHTML, msg);
                    }
                    if (invalidEmail) {
                        msg = "please enter a valid email address";
                        displayError(errors, elemHTML, msg);
                    }
                    if (submissionRequest) {
                        elemHTML.focus();
                    }
                } else {
                    if (errorDisplayed) {
                        hideErrors(errors, elemHTML)
                    }
                    if ((index === (elemsToCheck.length - 1)) && submissionRequest && (unicity && pwdIdentical)) {
                        /* submission has been requested
                        AND all unique inputs have been tested and have returned true
                        AND, if password input, passwords match */
                        form.submit();
                    }
                }
            });
    });
}

/**
 * Compare the values of pwd and pwdConfirm input fields
 * 
 * @return {boolean} - passwords validated
 */
function validatePasswords() {
    let valid = (pwdInput.value === pwdConfirmInput.value);
    let errors = pwdConfirmInput.parentElement.getElementsByClassName("errorMessage");
    if (errors.length > 0) {
        hideErrors(errors, pwdConfirmInput);
    }
    let msg = "the two passwords are different"
    if (!valid) {
        let errors = pwdConfirmInput.parentElement.getElementsByClassName("errorMessage");
        displayError(errors, pwdConfirmInput, msg);
    }
    return valid;
}

/**
 * Check if a string is in an email format
 * 
 * @param {string} email
 * 
 * @return {boolean}
 */
function validateEmail(email) {
    // Also checked backend at form submission
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*))@((([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * hide old errors, by calling hideErrors(), then displays a new error attached to a speĉific html tag
 *
 * @param {HTMLCollection} errors - previously displayed errors attached to elemHTML 
 * @param {HTMLElement} elemHTML - element to which attach the new error
 * @param msg {string} msg - message to display as an error
 * 
 */
function displayError(errors, elemHTML, msg) {
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
function hideErrors(errors, elemHTML) {
    elemHTML.style.borderColor = "";
    [...errors].forEach(error => {
        error.style.display = "none";
    });
}

</script>