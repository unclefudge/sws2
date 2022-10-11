import CustomQuestion from './custom-question.js';

export default {
    components: {CustomQuestion},
    template: '#custom-section-template',
    props: {
        active_field: String,
        section: Array,
        sections_count: Number,
    },
    data() {
        return {debug: true, open: false}
    },
    created() {
        if (window.location.hostname == 'safeworksite.com.au') this.debug = false;  // Disable debug on LIVE site
    },
    computed: {
        /*inProgressAssignments() {
            return this.assignments.filter(assignment => !assignment.complete);
        },*/
    },

    methods: {
        toggleSection(id) {
            alert(id);
            this.open = !this.open;
        }

    }
}