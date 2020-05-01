<template lang="html">
   
     <p>{{ unread }}</p>
</template>

<script>
    export default {
        props: ['friend', 'unread','value'],
        methods: {
            checkUser: function() {
                return _.find(this.unread, {id: this.friend.id});
            }
        },
        mounted() {
            console.log('Component unread mounted.')
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
