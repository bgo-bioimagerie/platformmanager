<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">

<div id="hider" class="col-xs-12"></div> 
<div id="popup_box" class="pm_popup_box" style="display: none;">    
    <div class="row">
        <div id="content_section" class="col-md-12" style="text-align:center;">
            <div id="news">
                <div class="col-md-1 col-md-offset-11" style="text-align: right;">
                    <a id="close"
                        v-on:click="closePopup"
                        class="bi-x-circle-fill"
                        style="cursor:pointer;">
                    </a>
                </div>
                <div v-for="news in newsList">
                    <img :src="news.media" alt="news image" style="max-width:320px; margin:5px"/>
                    <h3> {{ news.title }} </h3>
                    <div v-html="news.content" style="margin:25px">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

let popup_box = document.getElementById('popup_box');
let hider = document.getElementById('hider');

var newsView = new Vue({
    el: '#news',
    data: {
        newsList: new Array(),
    },
    methods: {
        getNewsData() {
            let headers = new Headers();
                headers.append('Content-Type','application/json');
                headers.append('Accept', 'application/json');
            let cfg = {
                headers: headers,
                method: 'GET',
            };
            fetch(`getnews/<?php echo $id_space ?>`, cfg).
                then((response) => response.json()).
                then(data => {
                    data.news.forEach((elem) => {
                        this.newsList.push({
                            "title": elem.title,
                            "content": elem.content,
                            "media": elem.media
                        });
                    });
                }).
                then(this.displayPopup());
        },
        displayPopup() {
            popup_box.style.display = "block";
            popup_box.style.opacity = 1;  
        },
        closePopup(event) {
            // hider.hide();
            // popup_box.hide();
            hider.style.opacity = 0;
            popup_box.style.opacity = 0;
            popup_box.style.display = "none";
            hider.style.display = "none";
        }
    },
    beforeMount() {
        this.getNewsData();
    }
})
</script>
