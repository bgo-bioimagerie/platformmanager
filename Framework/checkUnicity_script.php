<script>
// get const values userId, save btn and form elements
userId = document.getElementById("id").value;
form = document.getElementById("editForm");
saveBtn = document.getElementById("editFormsubmit");
inputs = form.getElementsByTagName("input");
isPwd = [...inputs].filter(input => input.id === "pwd").length > 0;
console.log("isPwd", isPwd);
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

function createErrorDiv(HTMLelement) {
    let error = document.createElement("div");
    error.className = "errorMessage notUnique";
    error.style.color = "red";
    error.style.display = "none";
    HTMLelement.parentElement.append(error);
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
 * @param string origin
 * 
 */
// TODO: rename, factorize
function validateUserForm(origin) {
    console.log("!origin.target", !origin.target);
    let unicity = true;
    let pwdValidity = true;
    let msg;
    if (!origin.target) {
        // not an onsubmit event
        uniqueElems.length = 0;
        uniqueElems.push(origin);
    } else {
        origin.preventDefault();
        // check if passwords match
        pwdValidity = isPwd ? validatePasswords() : true;
    }
    const headers = new Headers();
        headers.append('Content-Type','application/json');
        headers.append('Accept', 'application/json');
    const cfg = {
        headers: headers,
        method: 'GET',
    };
    
    // for each element fetch unicity info then display or delete warnings
    uniqueElems.forEach((elem, index) => {
        console.log("in forEach");
        const elemHTML = document.getElementById(elem.id);
        const value = elemHTML.value;
        const type = elemHTML.id;
        let errors = elemHTML.parentElement.getElementsByClassName("errorMessage");
        let errorDisplayed = (errors.length > 0);
        let invalidEmail = (type === "email") && !validateEmail(value);
        fetch(`isunique/` + type + "/" + value + "/" + userId, cfg).
            then((response) => response.json()).
            then(data => {
                console.log("data", data);
                if (!data.isUnique || invalidEmail /*|| passwordsDontMatch*/) {
                    // Data is not valid
                    unicity = false;
                    if (!data.isUnique && errorDisplayed) {
                        msg = "this " + type + " already exists"
                        displayError(errors, elemHTML, msg);
                    }
                    if (invalidEmail && errorDisplayed) {
                        msg = "please enter a valid email address"
                        displayError(errors, elemHTML, msg);
                    }
                    if (origin.target) {
                        elemHTML.focus();
                    }
                    
                } else {
                    if (errorDisplayed) {
                        hideErrors(errors, elemHTML)
                    }
                    if ((index === (uniqueElems.length - 1)) && origin.target && (unicity && pwdValidity)) {
                        /* submission button has been clicked
                        AND all unique inputs have been tested and have returned true
                        AND, if password input, passwords match */
                        console.error("submitting");
                        form.submit();
                    } else {
                        console.error("not submitting")
                    }
                }
            });
    });
}

/**
 * Compare the values of pwd and pwdConfirm input fields
 * 
 * @return bool
 */
function validatePasswords() {
    let valid = (pwdInput.value === pwdConfirmInput.value);
    let errors = pwdConfirmInput.parentElement.getElementsByClassName("errorMessage");
    if (errors.length > 0) {
        hideErrors(errors, pwdConfirmInput);
    }
    let msg = "the two passwords don't match"
    if (!valid) {
        let errors = pwdConfirmInput.parentElement.getElementsByClassName("errorMessage");
        displayError(errors, pwdConfirmInput, msg);
    }
    console.log("password validation", valid);
    return valid;
}

function validateEmail(email) {

    // Ajout en conf ?
    // const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*))@((([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function displayError(errors, elemHTML, msg) {
    [...errors].forEach(error => {
        error.style.display = "none";
    });
    let errorToShow = elemHTML.parentElement.getElementsByClassName("errorMessage")[0];
    errorToShow.innerHTML = msg;
    elemHTML.style.borderColor = "red";
    errorToShow.style.display = "block";
}

function hideErrors(errors, elemHTML) {
    elemHTML.style.borderColor = "";
    [...errors].forEach(error => {
        error.style.display = "none";
    });
}

</script>