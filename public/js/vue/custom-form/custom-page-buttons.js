//import Assignments from './assignments.js';
//import formPage from './form-page.js';
export default {
    //components: { formPage },
    template: '#custom-page-buttons-template',
    props: {
        pages: Array,
        active_page: String,
    },
    data() {
        return {
            //active_page: this.active_page,
            //pageTitle: 'Page Title2222',
        }
    },
    computed: {}, //v-on:click="showPage(page.order)"
    methods: {
        showpage(selected) {
            this.$emit('showpage', selected);
        }
    }
}