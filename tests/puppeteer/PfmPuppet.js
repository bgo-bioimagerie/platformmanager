import { createRequire } from "module";
const require = createRequire(import.meta.url);

const puppeteer = require('puppeteer');
const fs = require('fs');


 export default class PfmPuppet {
    password;
    login;
    host;
    browser = null;
    browserEndPoint = null;
    page = null;
    envVars = Object();
    
    /**
     * Initializes test environnement, launch browser and connect to pfm
     */
    async init() {
        this.getEnvVars(".env").then((response) => {
            this.envVars = response;
            this.login = this.envVars.PFM_ADMIN ?? "";
            this.password = this.envVars.PFM_ADMIN_PASSWORD ?? "";
            this.host = this.envVars.PFM_WEB_URL ?? "http://localhost:3000";
            console.log('envVars ok');
        }).catch(error => {
            console.error(error);
        });
        this.browser = await puppeteer.launch({headless: false});
        this.browserEndpoint = await this.browser.wsEndpoint();
        console.log('browser ok');
        [this.page] = await this.browser.pages();
        console.log('page created');
    }

    /**
     * Gets environnement variables
     * 
     * @param {string} path 
     * @returns {Promise<>}
     */
    async getEnvVars(path) {
        return new Promise((resolve) => {
            fs.readFile(path, (err, data) => {
                if (err) {
                    reject(err);
                }
                let lines = data.toString().split("\n");
                let keyValuePair = [];
                let result = Object();
                lines.forEach(element => {
                    if (element[0] === "#" || element === "") {
                        return;
                    }
                    keyValuePair = element.split("=");
                    result[keyValuePair[0].trim()] = keyValuePair[1].trim();
                });
                resolve(result);
            });
        });
    }

    /**
     * Tests connection to PFM
     */
    async connection() {
        console.log("connecting to PFM");
        await this.page.goto(this.host + "/coreconnection");
        try {
            console.log("typing login and password");
            let credentials = {login: this.login, password: this.password};
            await this.page.waitForSelector('#app');
            this.page.evaluate(cred => {
                document.getElementById('login').value = cred.login;
                document.getElementById('password').value = cred.password;
            }, credentials);
            await this.page.evaluate(() => document.getElementById('connectionBtn').click());
        } catch (err) {
            console.error("connection failed: ", err.message);
        }
    }

    /**
     * Tests menus and space creation
     * 
     * @param {SpaceConfig} spaceConfig
     * 
     */
    async createNewSpace(spaceConfig) {
        console.log("creating menu, space, submenu and item");
        await this.createMenu();
        await this.createSpace(spaceConfig);
        await this.checkSpaceCreation(spaceConfig);
    }

    async createMenu(menuName = "puppetMenu") {
        console.log("creating menu");
        this.browser = await puppeteer.connect({ browserWSEndpoint: this.browserEndpoint });
        [this.page] = await this.browser.pages();
        await this.page.goto(this.host + '/coremainmenus');

        try {
            console.log("accessing to menu creation page");
            await this.page.evaluate(() => document.getElementById('addmenu').click());
        
            console.log("filling menu form");
            await this.page.waitForSelector('#name');
            await this.page.evaluate(val => document.getElementById('name').value = val, menuName);
            await this.page.waitForSelector('#editmainmenuformsubmit');
            await this.page.evaluate(() => document.getElementById('editmainmenuformsubmit').click());
        } catch (err) {
            console.error("menu creation failed", err.message);
        }
    }

    /**
     * Creates a new space then checks if creation went right
     * 
     * @param {SpaceConfig} spaceConfig
     * 
     */
    async createSpace(spaceConfig) {
        console.log("creating space");
        this.browser = await puppeteer.connect({ browserWSEndpoint: this.browserEndpoint });
        [this.page] = await this.browser.pages();
        await this.page.goto(this.host + '/spaceadmin');

        try {
            console.log("accessing to space creation page");
            await this.page.evaluate(() => document.getElementById('addspace').click());

            console.log("filling space form");
            
            // Fill form fields
            await this.page.waitForSelector('#admins');
            await this.page.evaluate(config => {
                Object.entries(config).forEach((entry) => {
                    if (entry[0] != "adminFullName") {
                        document.getElementById(entry[0]).value = entry[1];
                    }
                });
                
               let element = document.getElementById('admins');
               let options = [...element.options].map(function(el) {
                    return {id: el.value, name: el.text};
               });
               element.value = options.find(option => option.name == config.adminFullName).id;
            }, spaceConfig);

            // Click save button
            await this.page.waitForSelector('#corespaceadmineditsubmit');
            await this.page.evaluate(val => document.getElementById('corespaceadmineditsubmit').click());

            // if "space already exist" page, log then continue
            let spaceCheck = await this.isErrorPage();
            if (spaceCheck.error) {
                throw Error(spaceCheck.text);
            }
        } catch (err) {
            console.error("space creation failed:", err.message);
        }
    }

    /**
     * Checks if space exists and has the right attributes
     * 
     * @param {SpaceConfig} spaceConfig
     * 
     */
    async checkSpaceCreation(spaceConfig) {
        // check if space exists and has the right attributes
        try {
            // go find our new space id
            await this.page.goto(this.host + '/spaceadmin');
            await this.page.waitForSelector('#app');
            let spaceId = await this.page.evaluate((config) => {
                let tableCells = document.getElementsByTagName('td');
                let newSpaceNameCell = [...tableCells].find(cell => {
                    return cell.innerText === config.name;
                });
                if (newSpaceNameCell) {
                    let newSpaceLine = newSpaceNameCell.parentElement;
                    return [...newSpaceLine.children].find(child => {
                        return child.innerText.includes("corespace");
                    }).innerText.split('corespace/').pop();
                } else {
                    throw Error("our new space hasn't been found");
                }
            }, spaceConfig);

            // navigate to its space edition page then compare its values with our config values
            await this.page.goto(this.host + '/spaceadminedit/' + spaceId);
            await this.page.waitForSelector('#app');

            let inputFields = {
                name: spaceConfig.name,
                contact: spaceConfig.contact,
                status: spaceConfig.status,
                support: spaceConfig.support
            };
            let selectors = {admins: spaceConfig.adminFullName};
            let isSpaceOk = this.compareWithFormValues(inputFields, selectors);
            if (!isSpaceOk) {
                throw Error("space data do not match");
            }
            console.log("space creation ok");
        } catch(err) {
            console.error("space check failed", err.message);
        }
    }

    ///// UTILS /////

    /**
     * Check if an object properties match in edition form
     * @typedef {Object} FormData
     * @property {key: value} property to check in form
     * 
     * @param {FormData} inputFields
     * @param {FormData} selectors 
     * @returns {Promise<Boolean>} dataMatch
     */
    async compareWithFormValues(inputFields = {}, selectors = {}) {
        let dataToCompare = {inputFields: inputFields, selectors: selectors}

        await this.page.waitForSelector('#app');
        return this.page.evaluate(data => {
            let dataMatch = true;
            
            Object.entries(data.inputFields).forEach((entry) => {
                // Test input fields
                if (document.getElementById(entry[0]).value != entry[1]) {
                    dataMatch = false;
                }
            });
            
            Object.entries(data.selectors).forEach((entry) => {
                // Test selectors
                let selector = document.getElementById(entry[0]);
                let selectedText = selector.options[selector.selectedIndex].text;
                if (selectedText != entry[1]) {
                    dataMatch = false;
                }
            });

            return dataMatch;
        }, dataToCompare);
    }

    /**
     * Extracts page name from an url:
     * - removes host name
     * - removes parameters
     * 
     * @param {string} url
     * @returns {string} page name
     */
    pageNameFromUrl(url) {
        let pageName = "";
        let str = url.split(this.host).pop();
        str = (str.charAt(0) === '/') ? str.slice(1) : str;
        pageName = str.includes('/') ? str.slice(0, str.indexOf('/')) : str;
        return pageName;
    }

    /**
     * Checks if current page is an error page
     * @typedef {Object} CustomError
     * @property {boolean} error - does an error text display?
     * @property {string} text - text displayed
     *  
     * @returns {CustomError} error
     */
    async isErrorPage() {
        await this.page.waitForSelector('#app');
        return this.page.evaluate(() => {
            let errorText = "";
            let error = document.getElementById('error'); 
            if (error) {
                errorText = document.getElementById('errorcontent').innerText;
            } else {
                error = false;
            }
            return {error: error, text: errorText};
        });
    }

    /**
     * Closes browser
     */
    async closeBrowser() {
        await this.browser.close();
    }

}
