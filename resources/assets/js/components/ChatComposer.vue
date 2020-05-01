 <template lang="html">
    <input type="text" v-on:keyup.enter="sendChat" v-model="chat" name="send-msg"  class="input-msg-send form-control" placeholder="Type message">
    

     
     
</template>

<script>
    export default {
        props: ['chats', 'userid', 'friendid'],
        data() {
            return {
                chat: ''
            }
        },

        methods: {
            sendChat: function(e) {
                const userId = $('meta[name="userId"]').attr('content');
                const friendId = $('meta[name="friendId"]').attr('content'); 
                if (this.chat != '') {
                    var  data = {
                        chat: this.chat,
                        friend_id: friendId,
                        user_id: userId,
                    }
                    this.chat = '';

                    axios.post('/chat/sendChat', data).then((response) => {
                        this.chats.push(data)
                    })
                }
            }
        }
    }
</script>

 

 
