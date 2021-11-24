const userSelector = document.getElementById("id_user");
const clientSelector = document.getElementById("id_client");
userSelector.setAttribute("onchange", "setClientsList(this.value)");

function setClientsList(userId) {
    console.log("in setClientsList:", userId);
    const spaceId = document.getElementById("id_space").value;
    console.log("id_space:", spaceId);
    const headers = new Headers();
            headers.append('Content-Type','application/json');
            headers.append('Accept', 'application/json');
    const cfg = {
        headers: headers,
        method: 'POST',
        body: null
    };
    cfg.body = JSON.stringify({
        id_user: userId,
        id_space: spaceId
    });
    fetch(`clientusersgetuserclients`, cfg, true).
        then((response) => response.json()).
        then(data => {
            console.log("data:", data);
            clientSelector.options.length = 0;
            data.user_clients.forEach( (userClient, index) => {
                console.log("id:", userClient.id)
                console.log("name:", userClient.name)
                clientSelector.options[index] = new Option(userClient.name, userClient.id);
            });
        });
}





