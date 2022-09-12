<?php require_once 'Modules/com/Model/ComTranslator.php'; ?>

<div id="compopup_box" class="modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo ComTranslator::News($lang) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div id="news" class="modal-body">
        <div v-for="news in newsList">
            <img v-if="news.media" :src="news.media" alt="news image" style="max-width:320px; margin:5px"/>
            <h3> {{ news.title }} </h3>
            <div v-html="news.content" style="margin:25px">
            </div>
        </div>
    </div>
    </div>
  </div>
</div>



<script>

$(document).ready(function(){
    newsView.getNewsData();
})

let newsView = Vue.createApp({
    data() {
        return {
            newsList: new Array()
        }
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
                    if (data.news) {
                        data.news.forEach((elem) => {
                            this.newsList.push({
                                "title": elem.title,
                                "content": elem.content,
                                "media": elem.media
                            });
                        });
                    }
                    return data;
                }).
                then(data => {
                    if (data.news.length > 0) {this.displayPopup();}
                });
        },
        displayPopup() {
            let myModal = new bootstrap.Modal(document.getElementById('compopup_box'))
            myModal.show();
        }
    },
    beforeMount() {
        
    }
}).mount('#news')
</script>
