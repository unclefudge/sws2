//import SelectPicker from './select-picker.js';
import CustomSelect from './custom-select.js';
import CustomSelect2 from './custom-select2.js';

export default {
    components: {CustomSelect, CustomSelect2},
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