//import SelectPicker from './custom-question.js';

export default {
    //components: {SelectPicker},
    template: '#custom-question-template',
    props: {
        active_field: String,
        question: Array,
    },
    data() {
        return {
            debug: true,
            open: false,
            select_array: [
                {key: '1', value: "One"},
                {key: '2', value: "Two"},
                {key: '3', value: "Three"},
            ],
        }
    },
    created() {
        if (window.location.hostname == 'safeworksite.com.au') this.debug = false; // Disable debug on LIVE site
    },
    computed: {
        questionType(type) {
            return 'tt';
        }
    },

    methods: {
        toggleQuestion(id) {
            alert(id);
            this.open = !this.open;
        },
        questionType(type) {
            return 'tt';
        },
        updateType() {
            alert('update select');
        }

    }
}