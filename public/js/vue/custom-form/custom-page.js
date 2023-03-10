import CustomSection from './custom-section.js';

export default {
    components: {CustomSection},
    template: '#custom-page-template',
    props: {
        active_field: String,
        page: Array,
        sections: Array,
    },
    data() {
        return {debug: true,}
    },
    created() {
        if (window.location.hostname == 'safeworksite.com.au') this.debug = false;  // Disable debug on LIVE site
    },
    computed: {
        /*activePageSections() {
            return this.sections.filter(section => section.page_id == this.page.id);
        },*/
    },

    methods: {
    },
}