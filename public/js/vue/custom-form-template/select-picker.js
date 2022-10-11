export default {
    template: '<select v-model="name" class="form-control" @change="function">' +
    '<option v-for="option in options" value="{{ option.value }}">{{{ option.text }}}</option>' +
    '</select>',
    name: 'selectpicker',
    props: ['options', 'name', 'function'],
    created () {
        // Init our picker
        $(this.$el).selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
        // Update whenever options change
        this.$watch('options', function (val) {
            // Refresh our picker UI
            $(this.$el).selectpicker('refresh');
            // Update manually because v-model won't catch
            this.name = $(this.$el).selectpicker('val');
        }.bind(this))
    }
}