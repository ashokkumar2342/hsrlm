 <template> 
      <li :class="selfFriend">
      	<div :class="selfFriendWrap">
      		<div :class="friendCircle">
			   <span class="initials"> 
			     {{ friendName | capitalize }} 
			   </span> 
			   
			 </div>
			 <!-- <img class="user-img img-circle block pull-left"  :src="'/dist/img/user1.png'" alt="user"/>  -->
      		<div class="msg block" :class="className"> <slot></slot>
      			<div class="msg-per-detail text-right">
      				<span class="msg-time txt-grey"><span>{{moment(chats.created_at).fromNow()}} </span></span>
      			</div>
      		</div>
      		<div class="clearfix"></div>
      	</div>	
      </li> 

</template>
<script>
	import moment from 'moment'
//one charactor 
  Vue.filter('capitalize', function (value) {
    if (!value) return ''
    value = value.toString()
    return value.charAt(0).toUpperCase()
  })
  // method
    export default { 
    	methods: {
    	  moment
    	},
        props: ['chats','userid', 'friendid'],
        computed:{ 
          className(){
          	const userId = $('meta[name="userId"]').attr('content');
            if (userId==this.userid) {
              return 'pull-right';
            }else{
              return 'pull-left';
            } 
          },
           selfFriend(){
           	const userId = $('meta[name="userId"]').attr('content');
            if (userId==this.userid) {
              return 'self';
            }else{
              return 'friend';
            } 
          },
           friendCircle(){
           	const userId = $('meta[name="userId"]').attr('content');
            if (userId==this.userid) {
              return 'hidden';
            }else{
              return 'avatar-circle  block pull-left';
            } 
          },  
          friendName(){
           	const name = $('meta[name="name"]').attr('content');

           return name;
          }, 
           selfFriendWrap(){
           	const userId = $('meta[name="userId"]').attr('content');

            if (userId==this.userid) {
              return 'self-msg-wrap';
            }else{
              return 'friend-msg-wrap';
            } 
          }
        },
        mounted() {
            console.log('Component Chat mounted.')
        }
    }


    
</script>

<style type="text/css">
	.avatar-circle {
     width: 50px;
    height: 50px;
    background-color: #f0c541;
    text-align: center;
    border-radius: 50%;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
  }
  .avatar-circle-agent {
     width: 50px;
    height: 50px;
    background-color: #2d8fce;
    text-align: center;
    border-radius: 50%;
    -webkit-border-radius: 50%;
    -moz-border-radius: 50%;
  }
  .initials {
    position: relative;
    top: 10px; /* 25% of parent */
    font-size: 30px; /* 50% of parent */
    line-height: 30px; /* 50% of parent */
    color: #fff;
    font-family: "Courier New", monospace;
    font-weight: bold;
    text-transform: uppercase;

  }
</style>
 
