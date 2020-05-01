<template lang="html">
    
    <div class="status online" v-if="checkUser()"></div>
    <div class="status offline" v-else></div>
</template>

<script>
    export default {
        props: ['friend', 'onlineusers'],
        methods: {
            checkUser: function() {
                return _.find(this.onlineusers, {id: this.friend.id});
            }
        }
    }
</script>

<style lang="css">
    .red {
        color: red;
    }
    .green {
        color: green;
    }
</style>
