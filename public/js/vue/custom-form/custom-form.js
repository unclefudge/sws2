//import Assignments from './assignments.js';
import CustomPageButtons from './custom-page-buttons.js';

export default {
    components: { CustomPageButtons },

    //template: `
    //Hello Template
    //`,
    data() {
        return {
            loading: false,
            active_page: 1,
            custom_form: [],
        }
    },
    created() {
        this.getTemplate();
    },

    computed: {
        inProgressAssignments() {
            //return this.assignments.filter(assignment => !assignment.complete);
        },

    },

    methods: {
        getTemplate() {

            // Get template data
            setTimeout(function () {
                this.loading = true;
                $.getJSON('/form/template/data/template/1', function (data) {
                    this.custom_form = data[0];
                    this.loading = false;
                    console.log(data[1]);

                }.bind(this));
            }.bind(this), 100);
        },
        showpage(selected) {
            this.active_page = selected;
        }
        /*
        handleFocus: function() {
          alert('in');
        },
        handleFocusOut: function() {
            alert('out');
        },
        handleClick: function(e) {
            alert('click');
            console.log(e)
        }*/
    }
}