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

    async closeBrowser() {
        await this.browser.close();
    }

    async getEnvVars(path) {
        return new Promise((resolve) => {
            fs.readFile(path, (err, data) => {
                if (err) {
                    throw("can't read file", err);
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

    async connection() {
        await this.page.goto(this.host);
        try {
            console.log("typing login and password");
            await this.page.waitForSelector('#login');
            await this.page.evaluate(val => document.querySelector('#login').value = val, this.login);
            await this.page.waitForSelector('#password');
            await this.page.evaluate(val => document.querySelector('#password').value = val, this.password);
            await this.page.evaluate(val => document.querySelector('button').click());
        } catch (err) {
            console.error("connection failed: ", err.message);
        }
    }

    async createNewSpace(spaceName = "spaceTest") {
        console.log("creating menu, space, submenu and item");
        await this.createMenu();
        await this.createSpace(spaceName);
    }

    async createMenu(menuName = "menuTest") {
        console.log("creating menu");
        this.browser = await puppeteer.connect({ browserWSEndpoint: this.browserEndpoint });
        [this.page] = await this.browser.pages();
        await this.page.goto(this.host + '/coremainmenus');

        try {
            console.log("accessing to menu creation page");
            await this.page.evaluate(val => document.querySelector('#addmenu').click());
        
            console.log("filling menu form");
            await this.page.waitForSelector('#name');
            await this.page.evaluate(val => document.querySelector('#name').value = val, menuName);
            await this.page.waitForSelector('#editmainmenuformsubmit');
            await this.page.evaluate(val => document.querySelector('#editmainmenuformsubmit').click());
        } catch (err) {
            console.error("menu creation failed", err.message);
        }
    }

    async createSpace(spaceName) {
        console.log("creating space");
        this.browser = await puppeteer.connect({ browserWSEndpoint: this.browserEndpoint });
        [this.page] = await this.browser.pages();
        await this.page.goto(this.host + '/spaceadmin');

        try {
            console.log("accessing to space creation page");
            await this.page.evaluate(val => document.querySelector('#addspace').click());

            console.log("filling space form");
            await this.page.waitForSelector('#name');
            await this.page.evaluate(val => document.querySelector('#name').value = val, spaceName);
            await this.page.waitForSelector('#editmainmenuformsubmit');
            await this.page.evaluate(val => document.querySelector('#corespaceadmineditsubmit').click());
        } catch (err) {
            console.error("connection failed", err.message);
        }
    }

    pageNameFromUrl(url) {
        let pageName = "";
        let str = url.split(this.host).pop();
        str = (str.charAt(0) === '/') ? str.slice(1) : str;
        pageName = str.includes('/') ? str.slice(0, str.indexOf('/')) : str;
        return pageName;
    }

    async clickButtonAndCheckRoute(buttonId, targetUrl) {
        console.log("in clickButtonAndCheckRoute()");
        // Doesn't work : buttonId is not defined => context problem ?
        await this.page.evaluate(val => document.querySelector('#' + buttonId).click());
        await this.page.on('load', () => {
            if (this.pageNameFromUrl(this.page.url()) != (targetUrl)) {
                throw Error("error trying to connect to coremainmenuedit");
            }
        });
    }

}
