<template>
  <v-layout row>
    <v-flex xs12 sm6 offset-sm3>
      <v-card class="chat-card" dark="">
        <v-list>
          <v-subheader
            >
              Group Chat
            </v-subheader>
            <v-divider></v-divider>
            <v-list
              class="p-3"
              v-for="(message, index) in allMessages"
              :key="index"
            >
                <div class="message-wrapper">
                    <v-flex>
                        <span class="small font-italic">{{message.user.name}}</span>
                    </v-flex>





                    <div v-if="message.message" class="text-message-container">
                        <v-chip color="green" text-color="white">
                            {{message.message}}

                        </v-chip>

                    </div>

                    <div class="image-container">
                        <img v-if="message.image" style="height: 100px"  :src="'/'+message.image" alt="">

                    </div>

                    <v-flex class="caption font-italic">
                        {{message.created_at}}
                    </v-flex>
                </div>


            </v-list>
        </v-list>
      </v-card>




    </v-flex>

      <div class="floating-div">
          <picker v-if="emoStatus" set="emojione" @select="onInput" title="Pick your emoji…" />

      </div>
    
      <v-footer
      height="auto"
      fixed
      color="grey"
      >
      <v-layout row >

        <v-flex class="ml-2 text-right" xs1>
              <v-btn @click="toggleEmo" fab dark small color="pink">
                  <v-icon>insert_emoticon </v-icon>
              </v-btn>
          </v-flex>

         <v-flex xs1 class="text-center">
             <file-upload
             post-action="/messages"
             ref='upload'
             @input-file="$refs.upload.active = true"
             :headers="{'X-CSRF-TOKEN': token}"
             >
                 <v-icon class="mt-3">attach_file</v-icon>
             </file-upload>

        </v-flex>

        <v-flex xs6 offset-xs3 justify-center align-center>
            <v-text-field
              rows=2
              v-model="message"
              label="Enter Message"
              single-line
              @keyup.enter="sendMessage"
            ></v-text-field>
        </v-flex>

        <v-flex xs2> 
            <v-btn 
             dark class="mt-3 ml-2 white--text" small color="green" @click="sendMessage">send</v-btn>
        </v-flex>
      </v-layout>
      
          
    </v-footer>
  </v-layout>
</template>

<script>
  import { Picker } from 'emoji-mart-vue'
  export default {
    props: ['user'],
    components:{
      Picker
    },
    data () {
      return {
        message:null,
        allMessages:[],
        emoStatus:false,
        token:document.head.querySelector('meta[name="csrf-token"]').content
        
      }
    },
    created(){
      this.fetchMessages();
      Echo.private('lchat')
      .listen('MessageSent',(e) => {
        console.log(e);
        this.allMessages.push(e.message)
        setTimeout(this.scrollToEnd,100);
      })
    },
    methods:{
     sendMessage(){
        //check if there message
        if(!this.message){
          return alert('Please enter message');
        }

        axios.post('/messages', {message: this.message}).then(response => {
                  this.message=null;
                  this.allMessages.push(response.data.message)
                  setTimeout(this.scrollToEnd,100);
        });

      },

      fetchMessages() {
          axios.get('/messages').then(response => {
              this.allMessages = response.data;
          });
      },

      scrollToEnd(){
        window.scrollTo(0,99999);
      },
      toggleEmo(){
            this.emoStatus= !this.emoStatus;
      },
      onInput(e){
        if(!e){
          return false;
        }
        if(!this.message){
          this.message=e.native;
        }else{
          this.message=this.message + e.native;
        }
      }
    
    
    
    },
    
    
  }
</script>

<style scoped>
.chat-card{
  margin-bottom:140px;
}
.floating-div{
    position: fixed;
}
.chat-card img {
    max-width: 300px;
    max-height: 200px;
}
</style>