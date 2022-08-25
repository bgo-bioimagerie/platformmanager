<script type="module">

import * as FEHandler from "./Framework/formErrorsHandler.js";

let inputs = document.getElementsByTagName('input');
let itemsToCompare = [];
[...inputs].forEach( input => {
    if (input.type.toLowerCase() == "email") {
        itemsToCompare.push(input);
    }
});

if (itemsToCompare[1]) {
    let email = itemsToCompare[0];
    let confirmEmail = itemsToCompare[1];
    FEHandler.createErrorDiv(itemsToCompare[1]);
    confirmEmail.addEventListener("blur", function () {
        let errors = itemsToCompare[1].parentElement.getElementsByClassName("errorMessage");
        if (email.value != confirmEmail.value) {
            FEHandler.displayError(errors, confirmEmail, "emails are not identical");
        } else {
            FEHandler.hideErrors(errors, confirmEmail);
        }
    });
}

</script>
