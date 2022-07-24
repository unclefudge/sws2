import AssignmentList from './assignmentlist.js';

export default {
    components: { AssignmenList },
    template: `
    <assignment-list :assignment="inProgressAssignments" title="In  Progress"></assignment-list>` ,

    data() {
        return {
            assignments: [
                { name: 'ass 1', complete: false, id: 1},
                { name: 'ass 2', complete: false, id: 2},
                { name: 'ass 3', complete: false, id: 3},
            ]
        }
    },

    computed() {
        inProgressAssignments() {
            return this

        },
    }
}