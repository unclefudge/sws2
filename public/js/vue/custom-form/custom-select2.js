//import CustomSection from './custom-section.js';

export default {
    template: '#custom-select2-template',
    data() {
        return {
            searchQuery: '',
            selectedItem: null,
            isVisible: false,
            itemsArray: [],
        }
    },
    computed: {
        filteredItems() {
            const query = this.searchQuery.toLowerCase();
            if (this.searchQuery === "") {
                return this.itemsArray;
            }
            return this.itemsArray.filter((item) => {
                return Object.values(item).some((word) => String(word).toLowerCase().includes(query));
            })
        },
    },
    methods: {
        selectItem(item) {
            this.selectedItem = item;
            this.isVisible = false;
        },
    },
    mounted() {
        fetch("https://jsonplaceholder.typicode.com/users")
            .then((res) => res.json())
            .then((json) => {
                console.log(json);
                this.itemsArray = json;
            })
    },
};