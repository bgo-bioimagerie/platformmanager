<script>
userId = document.getElementById("id").value;
form = document.getElementById("editForm");
saveBtn = document.getElementById("editFormsubmit");
let saveBtnAttr = getBtnAttr(saveBtn);
saveBtn.setAttribute("value", "");
saveBtn.setAttribute("type", "");
saveBtn.setAttribute("onclick", "checkUnicity(" + userId + ")");

/**
 * When called in a form, check input fileds of class "unique".
 * Setting checkUnicity param to true in Form::addEmail() or Form::addText() will lead to use this control function.
 * Forbid form submission and displays warns to user if a "unique" input field is filled with data already existing in database.
 * 
 * @param string userId
 * 
 */
function checkUnicity(userId) {
    console.log("checking form");
    const uniqueElems = document.getElementsByClassName("unique");
    console.log("elemsToCheck", uniqueElems);
    const headers = new Headers();
        headers.append('Content-Type','application/json');
        headers.append('Accept', 'application/json');
    const cfg = {
        headers: headers,
        method: 'GET',
    };
    
    // for each element fetch unicity info then display or delete warnings
    [...uniqueElems].forEach(elem => {
        console.log("in forEach");
        const elemHTML = document.getElementById(elem.id)
        const value = elemHTML.value;
        const type = elemHTML.id;
        let errors = elemHTML.parentElement.getElementsByClassName("errorMessage");
        let errorDisplayed = (errors.length > 0);
        let error = document.createElement("div");
        // TODO: place styles in a css file
        error.className = "errorMessage"
        error.style.color = "red";
        if (errorDisplayed) {
            elemHTML.style.borderColor = "";
            [...errors].forEach(error => {
                error.remove();
            });
        }

        // TODO: find a way to pass user id as a parameter
        fetch(`isunique/` + type + "/" + value + "/" + userId, cfg).
            then((response) => response.json()).
            then(data => {
                if (!data.isUnique) {
                    if (!errorDisplayed) {
                        // TODO: get a translator
                        error.innerHTML = "this " + type + " already exists";
                        elemHTML.style.borderColor = "red";
                        elemHTML.parentElement.append(error);
                    }
                } else if (type === "email" && !validateEmail(value)) {
                    if (error.innerHTML == "") {
                        elemHTML.style.borderColor = "red";
                        elemHTML.parentElement.append(error);
                        error.innerHTML = "please fill with a correct email";
                    }
                    
                } else {
                    // if (errorDisplayed) {
                    //     elemHTML.style.borderColor = "";
                    //     [...errors].forEach(error => {
                    //         error.remove();
                    //     });
                    // }
                    // reset saveBtn initial attributes
                    // saveBtn.setAttribute("value", saveBtnAttr.value);
                    // saveBtn.setAttribute("type", saveBtnAttr.type);
                    // saveBtn.click();
                }
            });
        
    });
}

/**
 * Save saveBtn attributes
 */
function getBtnAttr(saveBtn) {
    let type = saveBtn.getAttribute("type");
    let value = saveBtn.getAttribute("value");
    return {type: type, value: value}
}

function validateEmail(email) {
    console.log("email in validateEmail", email);
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

</script>