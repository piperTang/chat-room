new Vue({
    el :'#app',
    data:{
        messageList: [],      // 消息列表
        userList: [],       // 在线用户列表
        message: '',       // 消息框中的内容
        ws: null,           // websocket 对象
        layerHide: false,   // 是否隐藏登录框
        host: '127.0.0.1:8686',    // 服务器地址
        username: localStorage.getItem('username')    // 当前用户名
    },
    methods:{
        // 退出按钮
        logout: function() {
            localStorage.removeItem('username')
            this.username = undefined
            this.layerHide = false
            this.ws.close()
        },
        // 登录按钮
        dologin: function() {
            if(this.username != '')
            {
                localStorage.setItem('username', this.username)
                this.layerHide = true
                this.ws_conn()
            }
        },
        // 发消息按钮
        send: function() {
            if(this.message == '')
                return
            this.ws.send(this.message)
            this.message = ''
        },
        // 聊天列表滚动到底部
        scrollToBottom: function() {
            let d = document.querySelector('.message-list')
            d.scrollTop = d.scrollHeight
        },
        // 收到消息时调用
        ws_message: function(e){
            let data = JSON.parse(e.data)
            this.messageList.push(data)

            setTimeout(()=>{
                this.scrollToBottom()
            }, 100)

            if(data.allUsers)
            {
                this.userList = data.allUsers
            }
        },
        // 连接服务器
        ws_conn: function() {
            this.ws = new WebSocket('ws://'+this.host+'?username='+this.username)
            this.ws.onopen = this.ws_open
            this.ws.onmessage = this.ws_message
            this.ws.onclose = this.ws_close
        }
    },
    created:function () {
        if(localStorage.getItem('username'))
        {
            this.layerHide = true
            this.ws_conn()
        }
    },
});