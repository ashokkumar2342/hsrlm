 

require('./bootstrap');

window.Vue = require('vue');
//for scroll
import Vue from 'vue'
import VueChatScroll from 'vue-chat-scroll'
Vue.use(VueChatScroll) 
Vue.use(require('vue-moment'));
import moment from 'moment'
//for notification message and time out
import Toaster from 'v-toaster'
import 'v-toaster/dist/v-toaster.css'
Vue.use(Toaster, {timeout: 5000}) 
Vue.component('message', require('./components/MessageComponent.vue'));
 Vue.component('chat', require('./components/Chat.vue'));
 Vue.component('chat-composer', require('./components/ChatComposer.vue'));
Vue.component('onlineuser', require('./components/OnlineUser.vue'));
Vue.component('unread', require('./components/UnreadMessageCount.vue'));
 
 

const app = new Vue({

    el: '#app',
    data:{
        chats: '',
        onlineUsers: '', 
        status:'',
    	unread:'',
    	 
    },
    created() {
        const userId = $('meta[name="userId"]').attr('content');
        const friendId = $('meta[name="friendId"]').attr('content'); 
        if (friendId != undefined) {
            axios.post('/chat/getChat/' + friendId).then((response) => {
                this.chats = response.data;
            });
            Echo.private('Chat.' + friendId + '.' + userId)
                .listen('BroadcastChat', (e) => {
                      
                    document.getElementById('ChatAudio').play();
                    this.chats.push(e.chat); 
                    this.unread.push('1'); 
                    
                    //status changed
                    axios.post('/chat/status/' + friendId).then((response) => {
                        // this.status = response.data;
                        console.log('status changed');
                    });
                });
        }
          if (userId != 'null') {
            Echo.join('Online')
                .here((users) => {
                    this.onlineUsers = users;
                })
                .joining((user) => {
                    this.onlineUsers.push(user);
                    this.$toaster.success(user.name+' has joined chat room')
                })
                .leaving((user) => {
                    this.onlineUsers = this.onlineUsers.filter((u) => {u != user});
                    this.$toaster.warning(user.name+' left chat room')
                });
        }

    }
     
});
