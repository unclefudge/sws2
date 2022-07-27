export default {
    template: `
    <form @submit.prevent="add">
    <input v-model="newAssignment" type="text" placeholder="New assignment...">
    <button type="submit">Add</button>
    </form>
    `,

    data() {
        return {
            newAssignment: '',
        }
    },

    methods: {
        add() {
            this.$emit('add', this.newAssignment);
            /*
            this.assignments.push({
                name: this.newAssignment,
                completed: false,
                id: this.assignments.length + 1,
            });*/

            this.newAssignment = '';
        }
    }
}