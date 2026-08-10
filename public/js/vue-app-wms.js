Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

$.ajaxSetup({
    headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
});

function scrollToDiv(element) {
    element = element.replace("link", "");
    $('html,body').animate({scrollTop: $(element).offset().top}, 'slow');
}

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


// Move item up or down within the given array
function changeOrder(item_array, item, direction) {
    /// Move Up if item isn't 1st
    if (direction == '-' && item.order != 1) {
        for (var i = 0; i < item_array.length; i++) {
            if (item_array[i]['order'] == item.order - 1 && item_array[i]['step_id'] == item.step_id)
                item_array[i]['order'] = item.order;
        }
        item.order = item.order - 1;
    } else {
        var count = countItems(item_array, item.step_id);
        // Move Down if item isn't last
        if (direction == '+' && item.order < count) {
            for (var i = 0; i < item_array.length; i++) {
                if (item_array[i]['order'] == item.order + 1 && item_array[i]['step_id'] == item.step_id)
                    item_array[i]['order'] = item.order;
            }
            item.order = item.order + 1;
        }
    }
    xx.docModified = true;
}

// Delete item from array and reorder
function deleteItem(item_array, item) {
    var position = item.order;
    for (var i = 0; i < item_array.length; i++) {
        if (item_array[i]['order'] > position && item_array[i]['step_id'] == item.step_id)
            item_array[i]['order'] = item_array[i]['order'] - 1;
    }
    item_array.$remove(item);
    xx.docModified = true;
}

// Add item to array and immediately put the new item into edit mode.
// Vue 1 is more reliable here if we mutate the already-observed xx.edit
// object instead of replacing xx.edit with a brand new object.
function addItem(item_array, item) {
    item.id = nextID(item_array);

    var editPrefix = '';

    // If item is a Step add item to current position,
    // otherwise hazard/control are added to the end.
    if (item_array == xx.steps) {
        var position = item.order;

        for (var i = 0; i < item_array.length; i++) {
            if (item_array[i]['order'] >= position && item_array[i]['step_id'] == item.step_id)
                item_array[i]['order'] = item_array[i]['order'] + 1;
        }

        editPrefix = 's';
    } else if (item_array == xx.hazards) {
        editPrefix = 'h';
    } else if (item_array == xx.controls) {
        editPrefix = 'c';
    }

    // Add first so Vue has rendered the new row/item.
    item_array.push(item);

    // Then switch that item into edit mode using the existing reactive object.
    Vue.nextTick(function () {
        xx.edit.item = editPrefix + item.id;
        xx.edit.name = item.name || '';
        xx.edit.prin = item.res_principle || 0;
        xx.edit.comp = item.res_company || 0;
        xx.edit.work = item.res_worker || 0;
    });
}


function clearEdit() {
    xx.edit.item = '';
    xx.edit.name = '';
    xx.edit.prin = '';
    xx.edit.comp = '';
    xx.edit.work = '';
}

// Count items in array of certain type
function countItems(item_array, step_id) {
    var count = 0;
    if (!step_id) {
        count = item_array.length;
    } else {
        for (var i = 0; i < item_array.length; i++) {
            if (item_array[i]['step_id'] == step_id)
                count++;
        }
    }
    return count;
}

// Find the next possible ID in array
function nextID(item_array) {
    var id = 0;
    ;
    for (var i = 0; i < item_array.length; i++) {
        if (item_array[i]['id'] > id)
            id = item_array[i]['id'];
    }
    id = id + 1;
    return id;
}


var xx = {
    docModified: false, showConfirmPrinciple: false, showConfirmSignoff: false, showIncomplete: false,
    //edit_action: '', edit_item: '', edit_name: '', edit_prin: '',
    edit: {item: '', name: '', prin: '', comp: '', work: ''},
    user: {id: '', 'name': '', company_id: '', signoff: ''},
    company: {id: '', 'name': '', parent_id: '', parent_name: ''},
    doc: {id: '', name: '', comp_name: '', prin_name: ''},
    steps: [], hazards: [], controls: [],
};

Vue.component('app-wms', {
    template: '#wms-template',
    props: ['doc_id'],

    created: function () {
        this.getSteps();
    },
    data: function () {
        return {xx: xx};
    },
    components: {
        confirmPrinciple: VueStrap.modal,
        confirmSignoff: VueStrap.modal,
        incompleteForm: VueStrap.modal,
    },
    filters: {
        nl2br: function (string) {
            return string.replace(/\n/g, "<br />");
        },
    },
    methods: {
        getSteps: function () {
            $.getJSON('/safety/doc/wms/' + this.doc_id + '/steps/', function (data) {
                this.xx.doc = data[0];
                this.xx.steps = data[1];
                this.xx.hazards = data[2];
                this.xx.controls = data[3];
            }.bind(this));
        },
        saveDocumentDB: function () {
            xx.docModified = false;
            var docData = {
                action: 'save',
                doc: JSON.stringify(xx.doc),
                steps: JSON.stringify(xx.steps),
                hazards: JSON.stringify(xx.hazards),
                controls: JSON.stringify(xx.controls),
                _token: $('meta[name=token]').attr('value')
            };
            this.$http.patch('/safety/doc/wms/' + xx.doc.id, docData)
                .then(function (response) {
                    xx.doc.version = response.data;
                    toastr.success('Saved Document');
                }.bind(this)).catch(function (response) {
                alert('failed saving data to database');
            });
        },
        saveActiveDB: function () {
            this.xx.doc.status = 1;
            var docData = {
                action: 'save',
                doc: JSON.stringify(xx.doc),
                steps: JSON.stringify(xx.steps),
                hazards: JSON.stringify(xx.hazards),
                controls: JSON.stringify(xx.controls),
                _token: $('meta[name=token]').attr('value')
            };
            this.$http.patch('/safety/doc/wms/' + xx.doc.id, docData)
                .then(function (response) {
                    xx.doc.version = response.data;
                    toastr.success('Saved Document');
                    window.location = '/safety/doc/wms/' + xx.doc.id;
                }.bind(this)).catch(function (response) {
                alert('failed saving data to database');
            });
        },
        showConfirmSignoff: function () {
            if (xx.doc.res_compliance && xx.doc.res_review) {
                if (xx.docModified) {
                    setTimeout(function () {
                        this.xx.showConfirmSignoff = true;
                    }.bind(this), 3000);
                    this.saveDocumentDB();
                } else
                    this.xx.showConfirmSignoff = true;
            }
            else
                this.xx.showIncomplete = true;
        },
        cancelEdit: function () {
            clearEdit();
            xx.showConfirmPrinciple = false;
        },
        editDoc: function (doc) {
            if (xx.edit.item != 'd' + doc.id) {
                xx.edit.item = 'd' + doc.id;
                xx.edit.name = doc.name;
                xx.edit.prin = doc.project;
            }
        },
        saveDoc: function (doc, confirmed) {
            doc.name = xx.edit.name;
            doc.project = xx.edit.prin;
            clearEdit();
            xx.docModified = true;
            toastr.success('Updated document');

        },
        editPrinciple: function (doc) {
            if (xx.edit.item != 'p' + doc.id) {
                xx.edit.item = 'p' + doc.id;
                xx.edit.prin = doc.principle;
            }
        },
        savePrinciple: function (doc, confirmed) {
            if (xx.edit.prin != xx.company.parent_name && xx.showConfirmPrinciple == false) {
                xx.showConfirmPrinciple = true;
            } else {
                doc.principle = xx.edit.prin;
                if (confirmed) {
                    doc.principle_id = null;
                    doc.company_id = xx.company.id; // Make the for_company also the doc owner
                }
                if (xx.edit.prin == xx.company.parent_name) {
                    doc.principle_id = xx.company.parent_id; // Make the parent company the principle + doc owner
                    doc.company_id = xx.company.parent_id;
                }
                clearEdit();
                xx.docModified = true;
                xx.showConfirmPrinciple = false;
                toastr.success('Updated document');
            }
        },
        editStep: function (step) {
            if (xx.edit.item != 's' + step.id) {
                xx.edit.item = 's' + step.id;
                xx.edit.name = step.name;
            }
        },
        saveStep: function (step) {
            clearEdit();
            xx.docModified = true;
            toastr.success('Updated Step');
        },
        cancelStep: function (step) {
            step.name = xx.edit.name;
            clearEdit();
        },
        deleteStep: function (step) {
            deleteItem(xx.steps, step);
            toastr.error('Deleted Step');
        },
        orderStep: function (step, direction) {
            changeOrder(xx.steps, step, direction);
        },
        editFile: function (doc) {
            if (xx.edit.item != 'f' + doc.id) {
                xx.edit.item = 'f' + doc.id;
            }
        },
        saveFile: function (e) {
            e.preventDefault();

            var ext = '';
            var file = document.getElementById('attachment').files[0];
            if (file)
                ext = file.name.split('.').pop();


            if (ext.toLowerCase() == 'pdf' || ext == '') {
                var formData = new FormData();
                formData.append('attachment', file);
                formData.append('name', xx.doc.name);
                formData.append('principle', xx.doc.principle);
                formData.append('principle_id', xx.doc.principle_id);
                formData.append('company_id', xx.doc.company_id);
                formData.append('version', xx.doc.version);

                this.$http.post('/safety/doc/wms/' + xx.doc.id + '/upload', formData)
                    .then(function (response) {
                        clearEdit();
                        xx.docModified = false;
                        toastr.success('Saved document');
                        console.log(response.data);
                        xx.doc.attachment = response.data.attachment;
                        xx.doc.version = response.data.version;
                    }).catch(function (response) {
                    toastr.error('Upload failed');
                });
            } else
                toastr.error('File must be PDF');
        },
        addStep: function (position) {
            var item = {id: '', doc_id: xx.doc.id, name: '', order: position + 1, master: xx.doc.master, master_id: null, diff: 0};
            addItem(xx.steps, item);
        },
        addHazard: function (step_id) {
            var item = {id: '', step_id: step_id, name: '', order: countItems(xx.hazards, step_id) + 1, master: xx.doc.master, master_id: null, diff: 0};
            addItem(xx.hazards, item);
        },
        addControl: function (step_id) {
            var item = {id: '', step_id: step_id, name: '', order: countItems(xx.controls, step_id) + 1, master: xx.doc.master, master_id: null, res_principle: 0, res_company: 0, res_worker: 0, diff: 0};
            addItem(xx.controls, item);
        },
    },
});
/*
 * Hazards
 */
Vue.component('wms-hazards', {
    template: '#hazard-template',
    props: ['step_id'],

    data: function () {
        return {xx: xx};
    },
    filters: {
        nl2br: function (string) {
            return string.replace(/\n/g, "<br />");
        },
        filterStep: function (hazards) {
            return hazards.filter(function (hazard) {
                return hazard.step_id == this.step_id;
            }.bind(this));
        },
    },
    methods: {
        editHazard: function (hazard) {
            if (xx.edit.item != 'h' + hazard.id) {
                xx.edit.item = 'h' + hazard.id;
                xx.edit.name = hazard.name;
            }
        },
        saveHazard: function (hazard) {
            clearEdit();
            xx.docModified = true;
            toastr.success('Updated Hazard');
        },
        cancelHazard: function (hazard) {
            hazard.name = xx.edit.name;
            clearEdit();
        },
        cancelEdit: function () {
            clearEdit();
        },
        deleteHazard: function (hazard) {
            deleteItem(xx.hazards, hazard);
            toastr.error('Deleted Hazard');
        },
        orderHazard: function (hazard, direction) {
            changeOrder(this.xx.hazards, hazard, direction);
        }
    },
});
/*
 * Controls
 */
Vue.component('wms-controls', {
    template: '#control-template',
    props: ['step_id'],

    data: function () {
        return {xx: xx};
    },
    filters: {
        nl2br: function (string) {
            return string.replace(/\n/g, "<br />");
        },
        filterStep: function (controls) {
            return controls.filter(function (control) {
                return control.step_id == this.step_id;
            }.bind(this));
        },
    },
    methods: {
        responsibleName: function (control) {
            var string = '';
            if (control.res_principle)
                string = "Principal Contractor";
            if (control.res_company) {
                if (string)
                    string = string + ' & ' + xx.company.name;
                else
                    string = xx.company.name;
            }
            if (control.res_worker) {
                if (string)
                    string = string + ' & Worker';
                else
                    string = 'Worker';
            }
            return string;
        },
        editControl: function (control) {
            if (xx.edit.item != 'c' + control.id) {
                xx.edit.item = 'c' + control.id;
                xx.edit.name = control.name;
                xx.edit.work = control.res_worker;
                xx.edit.comp = control.res_company;
                xx.edit.prin = control.res_principle;
            }
        },
        saveControl: function (control) {
            control.res_worker = xx.edit.work;
            control.res_company = xx.edit.comp;
            control.res_principle = xx.edit.prin;
            clearEdit();
            xx.docModified = true;
            toastr.success('Updated Control');
        },
        cancelControl: function (control) {
            control.name = xx.edit.name;
            clearEdit();
        },
        cancelEdit: function () {
            clearEdit();
        },
        deleteControl: function (control) {
            deleteItem(xx.controls, control);
            toastr.error('Deleted Control');
        },
        orderControl: function (control, direction) {
            changeOrder(this.xx.controls, control, direction);
        }
    },
});

var myApp = new Vue({
    el: 'body',
    data: xx,
});



