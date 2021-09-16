<script>
userId = document.getElementById("id").value;
form = document.getElementById("editForm");
form.setAttribute("onsubmit", "return checkUnicity(event)");
saveBtn = document.getElementById("editFormsubmit");
uniqueElemsHTML = document.getElementsByClassName("unique");
uniqueElems = [...uniqueElemsHTML];
uniqueElems.forEach((elem) => {
    let elemId = elem.id; // why ?
    elem.setAttribute("onblur", "checkUnicity(" + elemId + ")");
});


/**
 * When called in a form, check input fileds of class "unique".
 * Setting checkUnicity param to true in Form::addEmail() or Form::addText() will lead to use this control function.
 * Forbid form submission and displays warns to user if a "unique" input field is filled with data already existing in database.
 * 
 * @param string userId
 * 
 */
// TODO: rename, factorize
function checkUnicity(origin) {
    // TODO: submit form if all fields ok
    console.log("!origin.target", !origin.target);
    let formOK = true;
    if (!origin.target) {
        // not an onsubmit event
        uniqueElems.length = 0;
        uniqueElems.push(origin);
    } else {
        origin.preventDefault();
    }
    const headers = new Headers();
        headers.append('Content-Type','application/json');
        headers.append('Accept', 'application/json');
    const cfg = {
        headers: headers,
        method: 'GET',
    };

    // check if there is a pwd input, then if passwords match
    let inputs = form.getElementsByTagName("input");
    let isPwd = [...inputs].filter(input => input.id === "pwd");
    let pwdValidity = isPwd ? validatePasswords() : true;
    
    // for each element fetch unicity info then display or delete warnings
    uniqueElems.forEach((elem, index) => {
        console.log("in forEach");
        const elemHTML = document.getElementById(elem.id);
        const value = elemHTML.value;
        const type = elemHTML.id;
        let errors = elemHTML.parentElement.getElementsByClassName("errorMessage");
        let errorDisplayed = (errors.length > 0);
        let error = document.createElement("div");
        // TODO: place styles in a css file
        error.className = "errorMessage"
        error.style.color = "red";
        let invalidEmail = (type === "email") && !validateEmail(value);
        fetch(`isunique/` + type + "/" + value + "/" + userId, cfg).
            then((response) => response.json()).
            then(data => {
                console.log("data", data);
                if (!data.isUnique || invalidEmail /*|| passwordsDontMatch*/) {
                    if (!data.isUnique && !document.getElementsByClassName("notUnique").length) {
                        [...errors].forEach(error => {
                            error.remove();
                        });
                        error.classList.add("notUnique");
                        error.innerHTML = "this " + type + " already exists";
                        elemHTML.style.borderColor = "red";
                        elemHTML.parentElement.append(error);
                    }
                    if (invalidEmail && !document.getElementsByClassName("invalid").length) {
                        [...errors].forEach(error => {
                            error.remove();
                        });
                        error.classList.add("invalid");
                        error.innerHTML = "please enter a valid email address";
                        elemHTML.style.borderColor = "red";
                        elemHTML.parentElement.append(error);
                    }
                    if (origin === "saveBtn") {
                        // elemHTML.focus();
                    }
                    formOK = false;
                } else {
                    if (errorDisplayed) {
                        elemHTML.style.borderColor = "";
                        [...errors].forEach(error => {
                            error.remove();
                        });
                    }
                    console.log("conditions", {originTarget: origin.target, index: index, uniqueElems: (uniqueElems.length -1), formOK: formOK, pwdValidity: pwdValidity});
                    if ((index === (uniqueElems.length - 1)) && origin.target && (formOK && pwdValidity)) {
                        // submission button has been clicked
                        // AND all unique inputs have been tested and have returned true
                        // AND, if password input, passwords match
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
    let pwdInput = document.getElementById("pwd");
    let pwdConfirmInput = document.getElementById("pwdconfirm");
    let valid = (pwdInput.value === pwdConfirmInput.value);
    if (!valid) {
        let error = document.createElement("div");
        error.className = "errorMessage"
        error.style.color = "red";
        error.classList.add("passwordsDontMatch");
        error.innerHTML = "the two password are different";
        pwdConfirmInput.style.borderColor = "red";
        pwdConfirmInput.parentElement.append(error);
    }
    console.log("password validation", valid);
    return valid;
}

function validateEmail(email) {
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

</script>