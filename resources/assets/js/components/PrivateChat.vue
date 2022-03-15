<template>
  <v-layout row>
    <v-flex class="online-users" xs3>
      <v-list>
          <v-list-tile
             :color="(friend.id==activeFriend)?'green':''"
             @click="activeFriend = friend.id"
             v-for="friend in friends"
            :key="friend.id"
          >
            <v-list-tile-action>
              <v-icon :color="(onlineFriends.find(onlineFriend => onlineFriend.id == friend.id)) ? 'green' : 'red'">account_circle</v-icon>
            </v-list-tile-action>

            <v-list-tile-content>
              <v-list-tile-title>{{friend.name}}</v-list-tile-title>
            </v-list-tile-content>

            <!-- <v-list-tile-avatar>
              <img :src="item.avatar">
            </v-list-tile-avatar> -->
          </v-list-tile>
        </v-list>

    </v-flex>
    
    <v-flex id="privateMessageBox" class="messages mb-5" xs9>
       <v-list>
            <v-list-tile
              class="p-3"
              v-if=""
              v-for="(message, index) in allMessages" 
              :key="index"
            >

           <v-layout 
           :align-end="(auth.id!==message.user.id)" 
            column
           >
             <v-flex>
               <v-layout column>
                  <v-flex>
                    <span class="small font-italic">{{message.user.name}}</span>
                  </v-flex>

                  <v-flex>
                    <v-chip
                      :color="(auth.id!==message.user.id)?'red':'green'"
                      text-color="white"
                    >

                      <v-list-tile-content >
                        {{message.message}}
                      </v-list-tile-content>
                    </v-chip>
                  </v-flex>

                  <v-flex class="caption font-italic">
                    {{message.created_at}}
                  </v-flex>
               </v-layout>
             </v-flex>
           </v-layout>


            </v-list-tile>
            <p v-if="typingFriend.name">{{typingFriend.name}} is typing</p>
        </v-list>

    
      <v-footer
      
      height="auto"
      fixed
      color="grey"
      >
      <v-layout row >
        <v-flex xs6 offset-xs3 justify-center align-center>
            <v-text-field
              rows=2
              v-model="message"
              label="Enter Message"
              single-line
              @keydown="onTyping"
              @keyup.enter="sendMessage"
            ></v-text-field>
        </v-flex>

        <v-flex xs2> 
            <v-btn 
              @click="sendMessage"
             dark class="mt-3 ml-2 white--text" small color="green">send</v-btn>
        </v-flex>
      </v-layout>
      
          
    </v-footer>
    
    
    </v-flex>

  </v-layout>
</template>

<script>
  export default {
    props:['auth'],
    
    data () {
      return {
        message:null,
        activeFriend:null,
        allMessages:[],
        users:[],
        typingFriend:{},
        typingClock:null,
        onlineFriends: []
      }
    },
    watch: {
      activeFriend(val){
        this.fetchMessages();
      }
    },
    methods:{
      onTyping(){
        Echo.private('privatechannel'+this.activeFriend).whisper('typing',{
          auth:this.auth
        });
      },
      sendMessage(){
        //check if there message
        if(!this.message){
          return alert('Please enter message');
        }
        if(!this.activeFriend){
          return alert('Please select friend');
        }
          axios.post('/private-messages/'+this.activeFriend, {message: this.message}).then(response => {
                    this.message=null;
                    this.allMessages.push(response.data.message)
                    setTimeout(this.scrollToEnd,100);
          });
      },
      fetchMessages() {
         if(!this.activeFriend){
           return ;
          }
            axios.get('/private-messages/'+this.activeFriend).then(response => {
                this.allMessages = response.data;
            });
      },
      fetchUsers() {
            axios.get('/users').then(response => {
                this.users = response.data;
                this.activeFriend=this.friends[0].id;
            });
        },
      scrollToEnd(){
        window.scrollTo(0,99999);
      }
    
    },
    computed:{
      friends(){
        return this.users.filter((user)=>{
          return user.id !==this.auth.id;
        })
      }
    },
    mounted(){
    },
    created(){
      this.fetchMessages();
      this.fetchUsers();

      Echo.join('plchat')
      .here((users) => {
           console.log(users);
           this.onlineFriends = users;
      })
      .joining((user) => {
          this.onlineFriends.push(user);
          console.log('joining',user.name);
      })
      .leaving((user) => {
          this.onlineFriends.splice(this.onlineFriends.indexOf(user),1)
          console.log('leaving',user.name);
      });

      Echo.private('privatechannel'+this.auth.id)
      .listen('PrivateMessageSent',(e) => {
        console.log(e);
        this.activeFriend=e.message.user_id;
        this.allMessages.push(e.message)
        setTimeout(this.scrollToEnd,100);
      })
      .listenForWhisper('typing', (e) => {
        if(e.auth.id == this.activeFriend){
            this.typingFriend=e.auth;
            if(this.typingClock) clearTimeout();
                this.typingClock=setTimeout(()=>{
                      this.typingFriend={};
                  },9000);
            }
           
      });
    },
    
  }
</script>

<style scoped>
.online-users,.messages{
  overflow-y:scroll;
  height:100vh;
}
</style>