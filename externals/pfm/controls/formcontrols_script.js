/**
 * FormControls checks some form parameters
 * based on attributes
 * 
 * * x-form in form elements, checks that all controls are ok before submit
 * * x-unique="xx" check unicity in db of input for type xxx
 * * x-id="xx" if present, for x-unique, gives user id
 * * x-equal="xx" check inputs with same xx key are equal
 * * x-email checks email input format
 * * x-suggest="aa,bb" suggest an input based in elements with id aa and bb (firstname, lastname)
 * 
 */
export class FormControls {

    constructor() {
        this.checks = {}
        this.editables = {}
        this.editableCounter = 0;
    }

    suggest(eltId, firstnameEltId, lastnameEltId) {
        let elt = document.getElementById(eltId)
        let firstNameElt = document.getElementById(firstnameEltId)
        let lastNameElt = document.getElementById(lastnameEltId)
        if(!firstNameElt.value || !lastNameElt.value) {
            return
        }
        elt.value = (firstNameElt.value[0] + lastNameElt.value.slice(0,10)).toLowerCase()
    }


    submit(form) {
        let ok = true
        let errors = Object.keys(this.checks)
        for(let i=0;i<errors.length;i++) {
            let error = this.checks[errors[i]]
            if (!error) {
                console.debug(`[controls] ${error} failed`)
                ok = false
                break
            }
        }
        console.debug('[controls] ok?', ok)
        let tag = form.id
        if(ok) {
            this.clearErrors(`form-${tag}`)
            form.submit();
        } else {
            this.setErrors([form], `form-${tag}`, "Form has errors")
        }
    }

    checkEmail(elt) {
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*))@((([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        let ok = re.test(String(elt.value).toLowerCase());
        let tag = elt.id;
        if(ok) {
            this.clearErrors(`email-${tag}`)
        } else {
            this.setErrors([elt], `email-${tag}`, "Invalid email format")
        }
        this.checks[`email-${tag}`] = ok
    }

    checkUnique(elt) {
        let tag = elt.getAttribute('x-unique')
        if(elt.value.length < 3) {
            return
        }
        let userId = null
        if (elt.hasAttribute('x-id')) {
            userId = elt.getAttribute('x-id')
        }
        this.isUnique(tag, elt.value, userId).then((ok) => {
            console.log('isunique?', ok)
            if(ok) {
                this.clearErrors(`unique-${tag}`)
            } else {
                this.setErrors([elt], `unique-${tag}`, "Already used")
            }
            this.checks[`unique-${tag}`] = ok
        })
    }

    isUnique(kind, value, userId) {
        return new Promise((resolve, reject) => {
            const headers = new Headers();
                headers.append('Content-Type','application/json');
                headers.append('Accept', 'application/json');
            const cfg = {
                headers: headers,
                method: 'POST',
                body: JSON.stringify({
                    type: kind,
                    value: value,
                    user: userId
                })
            };
            fetch(`coreaccountisunique`, cfg).
            then((response) => response.json()).
            then(data => {
                console.debug("data", data);
                resolve(data.isUnique)
            })
        })
    }

    checkEquals(tag) {
        let elts = document.querySelectorAll(`[x-equal='${tag}']`)
        let compareTo = null;
        let ok = true;
        elts.forEach(elt => {
            if(compareTo == null) {
                compareTo = elt.value
                return
            }
            if(elt.value === '') {
                return
            }
            if(elt.value != compareTo) {
                console.log('not equal', elt.value, compareTo)
                ok = false
            }
        })
        if(ok) {
            this.clearErrors(`equal-${tag}`)
        } else {
            this.setErrors(elts, `equal-${tag}`, "Inputs are different")
        }
        this.checks[`equal-${tag}`] = ok
    }

    setErrors(elements, errorTag, message) {
        let elts = document.querySelectorAll(`[x-error='${errorTag}']`)
        elts.forEach(elt => {
            elt.remove()
        })
        elements.forEach(elt => {
            let newError = document.createElement("div")
            newError.className = "alert alert-danger"
            newError.setAttribute('x-error', errorTag)
            newError.innerHTML = message
            elt.parentElement.append(newError);
        })
    }

    clearErrors(errorTag) {
        let elts = document.querySelectorAll(`[x-error='${errorTag}']`)
        elts.forEach(elt => {
            elt.remove()
        })
    }

    loadUniques() {
        let uniques = document.querySelectorAll('[x-unique]');
        if (uniques) {
            for(let i=0;i<uniques.length;i++) {
                let elt = uniques[i];
                elt.oninput = (event) => { let input = document.getElementById(event.target.id); this.checkUnique(input)};
            }
        }
    }

    loadEquals() {
        let equals = document.querySelectorAll('[x-equal]');
        if (equals) {
            for(let i=0;i<equals.length;i++) {
                let elt = equals[i];
                elt.oninput = (event) => { let input = document.getElementById(event.target.id); return this.checkEquals(input.getAttribute('x-equal'))};
            }
        }
    }

    loadEmails() {
        let emails = document.querySelectorAll('[x-email]');
        if (emails) {
            for(let i=0;i<emails.length;i++) {
                let elt = emails[i];
                elt.onchange = (event) => { let input = document.getElementById(event.target.id); return this.checkEmail(input)};
            }
        }
    }

    loadForms() {
        let forms = document.querySelectorAll('[x-form]');
        if (forms) {
            for(let i=0;i<forms.length;i++) {
                let elt = forms[i];
                elt.onsubmit = (event) => { event.preventDefault(); let input = document.getElementById(event.target.id); return this.submit(input)};
            }
        }
    }

    loadSuggests() {
        let suggests = document.querySelectorAll('[x-suggest]');
        if (suggests) {
            for(let i=0;i<suggests.length;i++) {
                let elt = suggests[i];
                let nameElts = elt.getAttribute('x-suggest').split(',')
                let firstNameElt = document.getElementById(nameElts[0])
                let lastNameElt = document.getElementById(nameElts[1])
                if(!firstNameElt || !lastNameElt) {
                    console.error('x-suggest error', nameElts)
                    continue;
                }
                firstNameElt.oninput = (event) => {
                    this.suggest(elt.id, firstNameElt.id, lastNameElt.id)
                }
                lastNameElt.oninput = (event) => {
                    if(event.target.value.length<3) {
                        return
                    }
                    this.suggest(elt.id, firstNameElt.id, lastNameElt.id)
                }
            }
        }
    }

    loadEditables() {
        let editables = document.querySelectorAll('[x-edit]');
        if(editables) {
            for(let i=0;i<editables.length;i++) {
                let editable = editables[i];
                let clone = editable.cloneNode(true);
                clone.setAttribute('id', clone.id + this.editableCounter);
                this.editableCounter += 1;
                this.editables[editable.id] = clone
                //editables[i].parentElement.classList.remove('col-md-10')
                //editables[i].parentElement.classList.add('col-md-8');
                let addButton = document.createElement("button");
                addButton.innerHTML="Add";
                addButton.classList.add('btn', 'btn-info')
                addButton.setAttribute('id', editable.getAttribute('id') + '_add');
                addButton.setAttribute('type', 'button')
                addButton.setAttribute('x-edit-id', editable.id)
                addButton.onclick = (evt) => {
                    let newElt = this.editables[evt.target.getAttribute('x-edit-id')].cloneNode(true)
                    let newId = newElt.id + this.editableCounter

                    newElt.setAttribute('id', newId);
                    this.editableCounter++;
                    
                    let newDelButton = document.createElement("span");
                    newDelButton.classList.add('glyphicon', 'glyphicon-trash')
                    newDelButton.setAttribute('type', 'button')
                    newDelButton.setAttribute('x-edit-id', newId)
                    newDelButton.onclick = (delevt) => {
                        console.log('del', delevt.target)
                        let elt = document.getElementById(delevt.target.getAttribute('x-edit-id'))
                        elt.remove();
                        delevt.target.remove();
                        
                    }

                    evt.target.insertAdjacentElement('beforebegin', newDelButton);
                    evt.target.insertAdjacentElement('beforebegin', newElt);

                }
                let delButton = document.createElement("span");
                delButton.classList.add('glyphicon', 'glyphicon-trash')
                delButton.setAttribute('type', 'button')
                delButton.setAttribute('x-edit-id', editable.id)
                delButton.onclick = (evt) => {
                    console.log('del', evt.target)
                    let elt = document.getElementById(evt.target.getAttribute('x-edit-id'))
                    elt.remove();
                    evt.target.remove();
                    
                }
                //editables[i]
                editable.insertAdjacentElement('beforebegin', delButton)
                editable.insertAdjacentElement('afterend', addButton)
                //editables[i].parentElement.after(addButton);
            }
        }   
    }

    load() {
        this.loadUniques()
        this.loadEquals()
        this.loadEmails()
        this.loadForms()
        this.loadSuggests()
        this.loadEditables()
    }
}


