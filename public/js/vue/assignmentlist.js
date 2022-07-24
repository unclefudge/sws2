export default {
    template: `
    <section>
                      <h2>{{ title }}</h2>
                            <ul>
                                <li v-for="assignment in assignments.filter(a => !a.complete)" :key="assignment.id">
                                    <label>
                                        <input type="checkbox" v-model="assignment.complete"> {{ assignment.name }}
                                    </label>
                                </li>
                            </ul>
                        </section>

    `,

    props: {
        assigmnents: Array,
        title: String,
    }
}