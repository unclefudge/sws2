//import CustomSection from './custom-section.js';

export default {
    template: '#custom-select-template',
    //name: "select1",
    emits: ["update:selected", "update:id", "update:name"],
    props: {
        selected: Object,
        urldata: String,
    },
    data: function () {
        return {
            options: [{name: 'one', id:1}, {name: 'two', id:2}, {name: 'three', id:3}],
        };
    },
    mounted() {
        //this.GetListData();
    },
    methods: {
        GetListData() {
            if (this.urldata !== "") {
                axios
                    .get(`${this.urldata}`)
                    .then((response) => (this.options = response.data.results));
            }
        },
        change(event) {
            console.log(this.selected);

            this.$emit("update:selected", this.selected);
            this.$emit("update:id", this.selected.id);
            this.$emit("update:name", this.selected.name);
        },
    },
}