import { createRequire } from "module";
const require = createRequire(import.meta.url);

const puppeteer = require('puppeteer');
const fs = require('fs');


 export default class PfmPuppet {

    password;
    login;
    host;
    browser = null;
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
            console.log("writing login");
            await this.page.evaluate(val => document.querySelector('#login').value = val, this.login);
            console.log("writing password");
            await this.page.evaluate(val => document.querySelector('#password').value = val, this.password);
            await this.page.evaluate(val => document.querySelector('button').click());
            this.page.on('load', () => {
                if (this.pageNameFromUrl(this.page.url()) === ('/corelogin' || '/coreconnection')) {
                    console.error("wrong login or password");
                } else {
                    console.log("successfully connected");
                }
            });
        } catch (err) {
            console.error("connection failed: ", err.message);
        }
    }

    pageNameFromUrl(url) {
        return url.split(this.host).pop();   
    }

}
