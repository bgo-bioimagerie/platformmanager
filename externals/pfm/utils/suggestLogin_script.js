/**
 * Automatically generates a login suggestion concatenating user's firstName first letter with user's whole name 
 */
export class SuggestLogin {

    constructor() {
        // get inputFields
        
        let firstNameInput = document.getElementById("firstname");
        let nameInput = document.getElementById("name");

        // Make them call action on blur
        nameInput.onblur = (event) => { this.suggestLogin(event); };
        firstNameInput.onblur = (event) => { this.suggestLogin(event); };
    }

    /**
     * Concatenate user's firstName first letter whith user's whole name 
     */
    suggestLogin(event) {
            let firstNameInput = document.getElementById("firstname");
            let nameInput = document.getElementById("name");
    
            let suggestedLogin, firstName, name;
            firstName = firstNameInput.value ?? '';
            firstName = firstName.replace(/\W/g, '');
            name = nameInput.value ?? '';
            name = name.replace(/\W/g, '');
            suggestedLogin = (firstName.charAt(0) + name).toLowerCase();
            let loginInput = document.getElementById("login");
            loginInput.value = suggestedLogin;
        }


}