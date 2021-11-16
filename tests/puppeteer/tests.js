import PfmPuppet from './PfmPuppet.js';

/**
 * Test chain
 */
 (async function main() {
    const puppet = new PfmPuppet();
    await puppet.init();
    await puppet.connection();
    // await puppet.createMenu();
})().catch(err => {
    console.error(err);
});
