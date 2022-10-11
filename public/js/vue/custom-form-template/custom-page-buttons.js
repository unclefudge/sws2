//import formPage from './form-page.js';
export default {
    //components: { formPage },
    template: '#custom-page-buttons-template',
    props: {
        pages: Object,
        active_page: Number,
    },
    data() {
        return {debug: true,}
    },
    created() {
        if (window.location.hostname == 'safeworksite.com.au') this.debug = false;  // Disable debug on LIVE site
    },
    computed: {}, //v-on:click="showPage(page.order)"
    methods: {
        showpage(selected) {
            this.$emit('showpage', selected);
        }
    }
}