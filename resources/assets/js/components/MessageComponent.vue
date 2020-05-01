<template>
     <li :class="selfFriend">
       <div :class="selfFriendWrap">  
          <!-- <img class="user-img img-circle block pull-left"  :src="'/dist/img/user1.png'" alt="user"/>  -->
        <div class="msg block" :class="className"><slot></slot>
           <div class="msg-per-detail text-right">
             <span class="msg-time txt-grey"> {{ user }} {{ time }}</span>
           </div>
         </div>
         <div class="clearfix"></div>
       </div>  
     </li>
</template>

<script>
    export default {
      props:[
        'color',
        'user',
        'time'
         
      ],
      computed:{
        className(){
          if (this.user=='you') {
            return 'pull-right';
          }else{
            return 'pull-left';
          } 
        },
         selfFriend(){
          if (this.user=='you') {
            return 'self';
          }else{
            return 'friend';
          } 
        }, 
         selfFriendWrap(){
          if (this.user=='you') {
            return 'self-msg-wrap';
          }else{
            return 'friend-msg-wrap';
          } 
        }
      },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
