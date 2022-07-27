export default {
    template: `
    <section v-show="assignments.length">
          <h2>{{ title }}  ({{ assignments.length}})</h2>

               <div class="flex">
               <button v-for="tag in tags" @click="currentTag = tag" class="button" style="margin-right: 5px">{{ tag }}</button>
               </div>
                          <ul>
                               <li v-for="assignment in filteredAssignments" :key="assignment.id">
                                 <label>
                                      <input type="checkbox" v-model="assignment.complete"> {{ assignment.name }}
                                   </label>
                               </li>
                           </ul>
   </section>
    `,

    props: {
        assignments: Array,
        title: String,
    },

    data() {
        return {
            currentTag: ''
        };
    },

    computed: {
        filteredAssignment() {
            return this.assignments.filter(a => a.tag === this.currentTag);
        },
        tags() {
            return ['math', 'science'];
            //return ...new Set(this.assignments.map(a => a.tag));
        },
    }
}