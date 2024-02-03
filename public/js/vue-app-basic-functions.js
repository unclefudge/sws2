Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

$.ajaxSetup({
    headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
});

var host = window.location.hostname;
var dev = true;
if (host == 'safeworksite.com.au')
    dev = false;

Vue.component('select-picker', {
    template: '<select v-model="name" class="form-control" @change="function">' +
        '<option v-for="option in options" value="{{ option.value }}">{{{ option.text }}}</option>' +
        '</select>',
    name: 'selectpicker',
    props: ['options', 'name', 'function'],
    ready: function () {
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
});

// Post data to url via POST method
function postAndRedirect(url, postData) {
    var postFormStr = "<form method='POST' action='" + url + "'>\n";

    for (var key in postData) {
        if (postData.hasOwnProperty(key))
            postFormStr += "<input type='hidden' name='" + key + "' value='" + postData[key] + "'></input>";
    }

    postFormStr += "</form>";
    var formElement = $(postFormStr);

    $('body').append(formElement);
    $(formElement).submit();
}

// Search through array of object with given 'key' and 'value'
function objectFindByKey(array, key, value) {
    for (var i = 0; i < array.length; i++) {
        if (array[i][key] == value) {
            return array[i];
        }
    }
    return null;
}