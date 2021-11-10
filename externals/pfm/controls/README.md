# controls

Javascript module to analyse some form parameters and check in pfm:

* input unicity (login, email)
* multiple inputs are equal (passwords for example)
* check there is no error in form
* suggests a login based on firstname and lastname

Include in html as:

    <script type="module" src='/externals/pfm/controls.js'></script>

To use webpack:

    npm run build

Then use main.js in dist directory
