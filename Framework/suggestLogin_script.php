<script type="text/javascript">
/**
 * Automatically generates a login suggestion concatenating user's firstName first letter with user's whole name 
 */

// get inputFields
let loginInput = document.getElementById("login");
let firstNameInput = document.getElementById("firstname");
let nameInput = document.getElementById("name");

// Make them call action on blur
nameInput.setAttribute("onblur", "suggestLogin()");
firstNameInput.setAttribute("onblur", "suggestLogin()");


/**
 * Concatenate user's firstName first letter whith user's whole name 
 */
function suggestLogin() {
    let suggestedLogin, firstName, name;
    firstName = firstNameInput.value ?? '';
    firstName = firstName.replace(/\W/g, '');
    name = nameInput.value ?? '';
    name = name.replace(/\W/g, '');
    
    suggestedLogin = (firstName.charAt(0) + name).toLowerCase();
    loginInput.value = suggestedLogin;
}

</script>
