import PfmPuppet from './PfmPuppet.js';

/**
 * Test chain
 */
 (async function main() {
    const spaceConfig = {
        name: "puppetSpace",
        adminFullName: "admin admin",
        contact: "test contact",
        status: 1,
        support: "support@test.org"
    };

    const puppet = new PfmPuppet();
    await puppet.init();
    await puppet.connection();
    await puppet.createNewSpace(spaceConfig);
})().catch(err => {
    console.error(err);
});
