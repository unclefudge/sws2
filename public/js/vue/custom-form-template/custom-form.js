//import Assignments from './assignments.js';
import CustomPageButtons from './custom-page-buttons.js';
import CustomPage from './custom-page.js';

export default {
    components: {CustomPageButtons, CustomPage},
    //template: `Hello Template`,

    data() {
        return {
            debug: true,
            loading: false,
            active_page: 1,
            active_field: '',
            custom_form: [],
        }
    },
    created() {
        this.getTemplate();
        if (window.location.hostname == 'safeworksite.com.au') this.debug = false; // Disable debug on LIVE site
    },
    computed: {
        activePage() {
            if (this.custom_form.pages) {
                var page = objectFindByKey(this.custom_form.pages, 'order', this.active_page);
                //console.log(page);
                return page;
            }
            return null; //page;
        },
        activeSections() {
            if (this.custom_form.sections) {
                return this.custom_form.sections.filter(page_id => this.active_page);
            }
            return null; //page;
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
                    //console.log(data[1]);

                }.bind(this));
            }.bind(this), 100);
        },
        showpage(selected) {
            this.active_page = selected;
        },
        editfield(field) {
            //alert(field);
            this.active_field = field;
        },
        saveData() {
            this.loading = true;
            this.saveDataDB(this.custom_form).then(function (result) {
                if (result) {
                    this.loading = false;
                }
            }.bind(this));
        },
        saveDataDB(custom_form) {
            return new Promise(function (resolve, reject) {
                var CSRF_TOKEN = $('meta[name=token]').attr('value');
                $.ajax({
                    url: '/form/template/data/save',
                    type: 'POST',
                    data: {_token: CSRF_TOKEN, custom_form: custom_form},
                    dataType: 'JSON',
                    success: function (result) {
                        console.log('DB saved');
                        resolve(result);
                    },
                    error: function (result) {
                        //alert("Failed to save data. Please try again");
                        console.log('DB save failed');
                        reject(false);
                    }
                });
            });
        },
    }
}