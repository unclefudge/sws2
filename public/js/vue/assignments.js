import AssignmentList from './assignmentlist.js';
import AssignmentCreate from './assignmentcreate.js';

export default {
    components: {AssignmentList, AssignmentCreate},
    template: `
    <assignment-list :assignments="inProgressAssignments" title="In Progress"></assignment-list>
    <assignment-list :assignments="completedAssignments" title="Completed"></assignment-list>

    <assignment-create @add="add"></assignment-create>
    `,

    data() {
        return {
            assignments: [
                {name: 'ass 1', complete: false, id: 1, tag: ['math']},
                {name: 'ass 2', complete: false, id: 2, tag: ['science']},
                {name: 'ass 3', complete: false, id: 3, tag: ['math']},
            ],
        }
    },

    computed: {
        inProgressAssignments() {
            return this.assignments.filter(assignment => !assignment.complete);
        },
        completedAssignments() {
            return this.assignments.filter(assignment => assignment.complete);
        },
    },

    methods: {
        add(name) {
            this.assignments.push({
                name: name,
                completed: false,
                id: this.assignments.length + 1,
            });
        }
    }
}