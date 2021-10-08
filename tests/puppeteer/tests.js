const puppeteer = require('puppeteer');
const fs = require('fs');

/**
 * Tests functions declarations
 */
 class PfmPuppet {

    constructor() {
        this.browser = null;
        this.page = null;
        this.envVars = Object();
    }
    async init() {
        this.getEnvVars(".env").then((response) => {
            this.envVars = response;
            console.log('envVars ok');
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
        await this.page.goto(this.envVars.PFM_WEB_URL || 'http://localhost:3000/');
        try {
            console.log("writing login");
            await this.page.evaluate(val => document.querySelector('.log').value = val, "pfmadmin");
            console.log("writing password");
            await this.page.evaluate(val => document.querySelector('.pass').value = val, "admin4genouest");
            this.page.evaluate(val => document.querySelector('button').click());
            console.log("connected");
        } catch (err) {
            console.error("connection failed", err.message);
        }
    }

}

/**
 * Test chain
 */
(async function main() {
    const puppet = new PfmPuppet();
    await puppet.init();
    await puppet.connection();
})().catch(err => {
    console.error(err);
});


