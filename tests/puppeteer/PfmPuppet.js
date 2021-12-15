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
            console.log("[CONF]", 'envVars ok');
        }).catch(error => {
            console.error("[CONF]", error);
        });
        this.browser = await puppeteer.launch({headless: false});
        this.browserEndpoint = await this.browser.wsEndpoint();
        console.log("[CONF]", 'browser ok');
        [this.page] = await this.browser.pages();
        console.log("[CONF]", 'page created');
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
        console.log("[CONNECTION]", "connecting to PFM");
        await this.page.goto(this.host + "/coreconnection");
        try {
            let credentials = {login: this.login, password: this.password};
            await this.page.waitForSelector('#app');
            this.page.evaluate(cred => {
                document.getElementById('login').value = cred.login;
                document.getElementById('password').value = cred.password;
            }, credentials);
            await this.page.evaluate(() => document.getElementById('connectionBtn').click());
        } catch (err) {
            console.error("[CONNECTION]", "connection failed: ", err.message);
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
        await this.createMenu(spaceConfig);
        await this.createSubMenu(spaceConfig);
        await this.createSpace(spaceConfig);
        await this.checkSpaceCreation(spaceConfig);
        await this.setSpaceBasicConfiguration(spaceConfig);
    }

    /**
     * Sets space basic configuration
     * 
     * @param {SpaceConfig} spaceConfig
     * 
     */
     async setSpaceBasicConfiguration(spaceConfig) {
        console.log("[SPACE CONFIG]", "setting up space basic configuration");
        try {
            let spaceId = await this.getTestSpaceId(spaceConfig.name);
            await this.page.goto(this.host + '/corespace/' + spaceId);
            await this.page.waitForSelector('#app');
            let modulesConfig = [
                {"name": "resources", "auth": 3},
                {"name": "clients", "auth": 3},
                {"name": "booking", "auth": 2},
            ]
            for await (const module of modulesConfig) {
                await this.createModule(module.name, module.auth, spaceId);
                console.log("[MODULES]", module.name + " configuration succeeded!");
            }
        } catch(err) {
            console.error("[SPACE]","basic space configuration failed", err.message);
        }
    }

    /**
     * Creates a space module
     * 
     * @param {string} moduleName
     * @param {int} authLevel
     * @param {int} spaceId
     * 
     */
     async createModule(moduleName, authLevel, spaceId) {
        console.log("[MODULES]", "creating module " +  moduleName);
        //activateModule
        try {
            await this.page.goto(this.host + '/' + moduleName + 'config/' + spaceId);
            await this.page.waitForSelector('#' + data.name + 'menustatus');
            await this.page.evaluate( data => {
                let roleMenu = document.getElementById(data.name + 'menustatus');
                let settingsRoleMenu = document.getElementById(data.name + 'settingsmenustatus') ?? null;
                roleMenu.value = data.auth;            
                if (settingsRoleMenu) {
                    settingsRoleMenu.value = (data.auth < 4) ? (data.auth + 1) : 4;
                }
            }, {name: moduleName, auth: authLevel});
            await this.page.evaluate(() => document.getElementById('menusactivationFormsubmit').click());
        } catch(err) {
            console.error("[MODULES]", moduleName + " configuration failed", err.message);
        }
    }

    /**
     * Creates a new menu 
     * 
     * @param {SpaceConfig} spaceConfig
     * @param {boolean} duplicates authorize duplication
     * 
     */
    async createMenu(spaceConfig, duplicates = false) {
        console.log("[MENUS]", "creating menu");
        let menuName = spaceConfig.name + "Menu";
        this.browser = await puppeteer.connect({ browserWSEndpoint: this.browserEndpoint });
        [this.page] = await this.browser.pages();
        await this.page.goto(this.host + '/coremainmenus');

        try {
            let createMenu = true;
            if (!duplicates) {
                let menuExists = await this.checkExistenceInTable(spaceConfig.name + "Menu");
                createMenu = !menuExists;
            }
            
            if (createMenu) {
                await this.page.evaluate(() => document.getElementById('addmenu').click());
                await this.page.waitForSelector('#name');
                await this.page.evaluate(val => document.getElementById('name').value = val, menuName);
                await this.page.waitForSelector('#editmainmenuformsubmit');
                await this.page.evaluate(() => document.getElementById('editmainmenuformsubmit').click());
            } else {
                console.log("[MENUS]", "menu " + (spaceConfig.name + "Menu") + " already exists");
            }
            // TODO: add properties verification script
        } catch (err) {
            console.error("[MENUS]", "menu creation failed", err.message);
        }
    }

    /**
     * Creates a new subMenu 
     * 
     * @param {SpaceConfig} spaceConfig
     * 
     */
    async createSubMenu(spaceConfig, duplicates = false) {
        console.log("[MENUS]", "creating subMenu");
        let subMenuName = spaceConfig.name + "subMenu";
        this.browser = await puppeteer.connect({ browserWSEndpoint: this.browserEndpoint });
        [this.page] = await this.browser.pages();
        await this.page.goto(this.host + '/coremainsubmenus');

        try {
            let subMenuExists = await this.checkExistenceInTable(spaceConfig.name + "subMenu");
            if (!subMenuExists) {
                await this.page.evaluate(() => document.getElementById('addsubmenu').click());
                await this.page.waitForSelector('#name');
                await this.page.evaluate(val => document.getElementById('name').value = val, subMenuName);
                
                // set parent menu
                await this.page.waitForSelector('#id_main_menu');
                await this.page.evaluate(config => {
                    let mainMenuSelector = document.getElementById('id_main_menu');
                    let options = [...mainMenuSelector.options].map(function(el) {
                        return {id: el.value, text: el.text};
                });
                    mainMenuSelector.value = options.find(option => option.text == (config.name + "Menu")).id ;
                }, spaceConfig);
                await this.page.evaluate(() => document.getElementById('editmainsubmenuformsubmit').click());
            } else {
                console.log("[MENUS]", "subMenu " + (spaceConfig.name + "subMenu") + " already exists");
            }
            // TODO: add properties verification script
        } catch (err) {
            console.error("[MENUS]", "submenu creation failed", err.message);
        }
    }

    /**
     * Creates a new space then checks if creation went right
     * 
     * @param {SpaceConfig} spaceConfig
     * 
     */
    async createSpace(spaceConfig, duplicates = false) {
        console.log("[SPACES]", "creating space");
        this.browser = await puppeteer.connect({ browserWSEndpoint: this.browserEndpoint });
        [this.page] = await this.browser.pages();
        await this.page.goto(this.host + '/spaceadmin');

        try {
            let spaceExists = await this.checkExistenceInTable(spaceConfig.name);
            if (!spaceExists) {
                await this.page.evaluate(() => document.getElementById('addspace').click());
                await this.page.waitForSelector('#admins');
                await this.page.evaluate(config => {
                    Object.entries(config).forEach((entry) => {
                        if (entry[0] != "adminFullName") {
                            document.getElementById(entry[0]).value = entry[1];
                        }
                    });
                    
                let element = document.getElementById('admins');
                let options = [...element.options].map(function(el) {
                        return {id: el.value, text: el.text};
                });
                element.value = options.find(option => option.text == config.adminFullName).id;
                }, spaceConfig);

                // Click save button
                await this.page.waitForSelector('#corespaceadmineditsubmit');
                await this.page.evaluate(val => document.getElementById('corespaceadmineditsubmit').click());

                // if "space already exist" page, log then continue
                let spaceCheck = await this.isErrorPage();
                if (spaceCheck.error) {
                    throw Error(spaceCheck.text);
                }
            } else {
                console.log("[SPACES]", "space " + (spaceConfig.name) + " already exists");
            }
        } catch (err) {
            console.error("[SPACES]", "space creation failed:", err.message);
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
            let spaceId = await this.getTestSpaceId(spaceConfig.name);
            await this.page.goto(this.host + '/spaceadminedit/' + spaceId);
            await this.page.waitForSelector('#app');

            let inputFields = {
                name: spaceConfig.name,
                contact: spaceConfig.contact,
                status: spaceConfig.status,
                support: spaceConfig.support
            };

            let selectors = {admins: spaceConfig.adminFullName};
            let isSpaceOk = await this.compareWithFormValues(inputFields, selectors);
            if (!isSpaceOk) {
                throw Error("space data do not match");
            }
            console.log("[SPACES]", "space creation ok");
        } catch(err) {
            console.error("[SPACES]", "space check failed", err.message);
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
        let result;
        let dataToCompare = {inputFields: inputFields, selectors: selectors};
        await this.page.waitForSelector('#app');
        try {
            result = await this.page.evaluate(data => {
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
        } catch(err) {
            console.log("object comparison failed:", err);
        }
        return result;
    }

    /**
     * Checks if a menu, subMenu or space already exists in its listing page
     * 
     * @param {string} name of element to find
     * 
     * @returns {async boolean}
     * 
     */
    async checkExistenceInTable(name) {
        return this.page.evaluate((needle) => {
            let tableCells = document.getElementsByTagName('td');
            let menuFound = [...tableCells].find(cell => {
                return cell.innerText === needle;
            });
            return menuFound ? true : false;
        }, name);
    }

    /**
     * Gets spaceId from spaceName
     * 
     * @param {string} spaceName 
     * @returns {asyncstring}
     */
    async getTestSpaceId(spaceName) {
        let spaceId;
        await this.page.goto(this.host + '/spaceadmin');
        await this.page.waitForSelector('#app');
        try {
            spaceId = await this.page.evaluate((name) => {
                let tableCells = document.getElementsByTagName('td');
                let spaceNameCell = [...tableCells].find(cell => {
                    return cell.innerText === name;
                });
                if (spaceNameCell) {
                    let spaceLine = spaceNameCell.parentElement;
                    return [...spaceLine.children].find(child => {
                        return child.innerText.includes("corespace");
                    }).innerText.split('corespace/').pop();
                } else {
                    throw Error("test space hasn't been found");
                }
            }, spaceName);
        } catch(err) {
            console.log("fetching spaceId failed:" + err);
        }
        return spaceId;
    }

    /**
     * Extracts page name from an url:
     * - removes host name
     * - removes parameters
     * 
     * @param {string} url
     * @returns {string}
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
     * @returns {async CustomError} error
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
