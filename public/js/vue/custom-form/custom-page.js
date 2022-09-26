//import Assignments from './assignments.js';
//import formPage from './form-page.js';

export default {
    //components: { formPage },

    template: `
    Page Template<br>

    `,
    props: {
        pages: Array,
        title: String,
    },
    data() {
        return {
            templatePage: [],
        }
    },
    computed: {
        inProgressAssignments() {
            //return this.assignments.filter(assignment => !assignment.complete);
        },

    },

    methods: {

    }
}