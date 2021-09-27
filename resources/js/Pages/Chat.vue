<template>
    <app-layout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-3 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div>
                        <div class="font-bold text-4xl">header</div>
                        <div>state</div>
                        <div>{{ info }}</div>
                        <div class="flex">
                            <div id="container"
                                 class="border-2 border-blue-100 border-collapse h-64 overflow-scroll w-9/12">
                                <div class="flex p-1" v-for="item in this.msgList" :key="item.id">
                                    <div class="pr-1" v-bind:class="isMine(item.title)">{{ item.title }}:</div>
                                    <div class="">{{ item.body }}</div>
                                </div>
                            </div>
                            <div class="border-2 ml-4 w-3/12">
                                <ul v-for="item in this.memberList" :key="item.id">
                                    <li>{{ item.name }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-2">
                            <input class="w-full border-blue-100" @keypress="sendByEnter" v-model="this.msg"
                                   type="text"/>
                        </div>
                        <div class="text-right mt-4">
                            <button @click="Leave" class="bg-gray-500 rounded px-3 py-1 hover:bg-gray-600 mx-4">Leave
                            </button>
                            <button @click="sendMsg" class="bg-yellow-500 rounded px-3 py-1 hover:bg-yellow-300">发送
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import { defineComponent } from 'vue';
import AppLayout from '@/Layouts/AppLayout';
import Echo from 'laravel-echo';
import { usePage } from '@inertiajs/inertia-vue3';

// noinspection ES6UnusedImports
import Pusher from 'pusher-js';

export default defineComponent({
    data () {
        return {
            chatRoomName: 'chatroom-001',
            echo: null,
            info: 'init',
            chatChannel: null,
            msgList: [],
            msgCount: 0,
            msg: '',
            memberList: [],
        };
    },
    components: {
        AppLayout,
    },
    watch: {
        msgList: { handler: function () {
            setTimeout( () => {
                this.toBottom();
            }, 100);
            }, deep: true }

    },
    mounted () {
        this.echo = new Echo({
            broadcaster: 'pusher',
            key: process.env.MIX_PUSHER_APP_KEY,
            cluster: process.env.MIX_PUSHER_APP_CLUSTER,
            wsHost: window.location.hostname,
            wsPort: 6001,
            forceTLS: false,
            disableStats: true,
        });

        this.chatChannel = this.echo.join(this.chatRoomName)
            .here((data) => {
                this.memberList = data;
            })
            .joining((data) => {
                this.info = 'joining';
                console.log(data);
                this.memberList.push(data);
            })
            .leaving((data) => {
                this.info = 'leaving';
                console.log(data);
                this.memberList = this.memberList.filter(function (value, index, arr) {
                    return value.id !== data.id;
                });
            })
            .listenForWhisper(this.chatRoomName, (msg) => {
                this.msgList.push(msg);
            })
        ;
    },
    methods: {
        sendMsg () {
            if (this.msg.length === 0) {
                alert('msg empty');
                return;
            }
            let msg = {
                'title': usePage().props.value.user.name,
                'body': this.msg,
                'id': Date.now(),
            };
            this.msgList.push(msg);
            this.chatChannel.whisper(this.chatRoomName, msg);
            this.msg = '';
        }
        ,
        Leave () {
            if (this.chatChannel) {
                this.echo.leave(this.chatRoomName);
                this.chatChannel = null;
            }
        }
        ,
        toBottom () {
            let container = this.$el.querySelector('#container');
            container.scrollTop = container.scrollHeight + 100;
            console.log('to bottom');
        }
        ,
        /** @param e KeyboardEvent */
        sendByEnter (e) {
            if (e.keyCode === 13) {
                this.sendMsg();
            }
        }
        ,
        isMine (title) {
            return title === usePage().props.value.user.name ? 'bg-blue-100' : '';
        }
    }
})
;
</script>
