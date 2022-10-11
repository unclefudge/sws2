import CustomQuestion from './custom-question.js';

export default {
    components: {CustomQuestion},
    template: '#custom-section-template',
    props: {
        active_field: String,
        section: Array,
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
        activeField(field) {
            return (field == this.active_field) ? true : false;
        },
        editfield(field) {
            this.$emit('editfield', field);
        },
        toggleSection(id) {
            alert(id);
            this.open = !this.open;
        }

    }
}