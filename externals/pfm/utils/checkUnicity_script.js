

export class CheckUnicity {

    constructor() {
        this.userId = 0;
        // get const values userId, save btn and form elements
        if (document.getElementById("id")) {
            this.userId = document.getElementById("id").value;
        }

        let form = document.getElementById("editForm")
            || document.getElementById("createuseraccountform")
            || document.getElementById("createaccountform")
            || document.getElementById("usermyaccountedit");
            
        let inputs = form ? form.getElementsByTagName("input") : [];
        let isPwd = [...inputs].filter(input => input.id === "pwd").length > 0;

        // check elements when submitting form
        if(form) {
            form.onsubmit = (e) => {
                return this.validateUserForm(e);
            }
        }


        // prepare unique elements checking
        let uniqueElemsHTML = document.getElementsByClassName("unique");
        let uniqueElems = [...uniqueElemsHTML];
        uniqueElems.forEach((elem) => {
            let elemId = elem.id; // why passing elem.id directly returns another value ??? Does it ?
            // check elem value when focusing out
            elem.onblur = (e) => { this.validateUserForm(elemId) }; 
            // create error div to display warnings to the user. Hide it
            this.createErrorDiv(elem);
        });

        // prepare password checking
        if (isPwd) {
            let pwdInput = document.getElementById("pwd");
            pwdInput.onblur = (e) => { this.validatePasswords() };
            let pwdConfirmInput = document.getElementById("pwdconfirm");
            pwdConfirmInput.onblur = (e) => { this.validatePasswords() };
            this.createErrorDiv(pwdConfirmInput);
        }
    }

    /**
     * Create an div of classes errorMessage and notUnique
     * append to the parentElement of the specified HTMLElement
     * 
     * @param {HTMLElement} element
     * 
     */
    createErrorDiv(element) {
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
    validateUserForm(origin) {
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
            pwdIdentical = isPwd ? this.validatePasswords() : true;
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
            let invalidEmail = (type === "email") && !this.validateEmail(value);
            fetch(`coreaccountisunique`, cfg, true).
                then((response) => response.json()).
                then(data => {
                    if (!data.isUnique || invalidEmail) {
                        // Data is not valid
                        unicity = false;
                        if (!data.isUnique) {
                            // TODO: find a way to get values from CoreTranslator !!!
                            msg = "this " + type + " already exists";
                            this.displayError(errors, elemHTML, msg);
                        }
                        if (invalidEmail) {
                            msg = "please enter a valid email address";
                            this.displayError(errors, elemHTML, msg);
                        }
                        if (submissionRequest) {
                            elemHTML.focus();
                        }
                    } else {
                        if (errorDisplayed) {
                            this.hideErrors(errors, elemHTML)
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
    validatePasswords() {
        let pwdInput = document.getElementById("pwd");
        let pwdConfirmInput = document.getElementById("pwdconfirm");

        let valid = (pwdInput.value === pwdConfirmInput.value);
        let errors = pwdConfirmInput.parentElement.getElementsByClassName("errorMessage");
        if (errors.length > 0) {
            this.hideErrors(errors, pwdConfirmInput);
        }
        let msg = "the two passwords are different"
        if (!valid) {
            errors = pwdConfirmInput.parentElement.getElementsByClassName("errorMessage");
            this.displayError(errors, pwdConfirmInput, msg);
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
    validateEmail(email) {
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
    displayError(errors, elemHTML, msg) {
        this.hideErrors(errors, elemHTML);
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
    hideErrors(errors, elemHTML) {
        elemHTML.style.borderColor = "";
        [...errors].forEach(error => {
            error.style.display = "none";
        });
    }

}