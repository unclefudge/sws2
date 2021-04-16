@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Safety tips</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject font-green-haze bold uppercase">Safety Tips</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasPermission2('add.safetytip'))
                                <a v-on:click="$root.$broadcast('add-tip-modal')" class="btn btn-circle green btn-outline btn-sm" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <!-- List Tips -->
                        <app-tips></app-tips>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="tips-template">
        <tip-modal :show.sync="showTipModal"></tip-modal>
        <input v-model="store.user_id" type="hidden" id="user_id" value="{{ Auth::user()->id }}">
        <input v-model="store.user_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">
        <input v-model="store.company_id" type="hidden" id="company_id" value="{{ Auth::user()->company->reportsTo()->id }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <table v-show="list.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th></th>
                            <th width="20%"> Title</th>
                            <th> Tip</th>
                            <th width="15%"> Last Published</th>
                            <th width="10%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="tip in list">
                            <tr>
                                <td>
                                    <span v-show="tip.id == store.active_tip">
                                        <i class="fa fa-circle" style="color: #32c5d2;"></i>
                                    </span>
                                    @if(Auth::user()->hasPermission2('del.safetytip'))
                                        <span v-show="tip.id != store.active_tip" v-on:click="setTipActive(tip)">
                                            <i class="fa fa-circle" style="color: #eee;"></i>
                                        </span>
                                    @else
                                        <span v-show="tip.id != store.active_tip">
                                            <i class="fa fa-circle" style="color: #eee;"></i>
                                        </span>
                                    @endif
                                </td>
                                <td>@{{ tip.title }}</td>
                                <td>@{{ tip.body }}</td>
                                <td>@{{ tip.niceDate }}</td>
                                <td>
                                    @if(Auth::user()->hasPermission2('edit.safetytip'))
                                        <button v-on:click="$root.$broadcast('edit-tip-modal', tip)" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                            <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                        </button>
                                    @endif
                                    @if(Auth::user()->hasPermission2('del.safetytip'))
                                        <button v-on:click="$root.$broadcast('del-tip-modal', tip)" class="btn dark btn-xs sbold margin-bottom btn-delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endif

                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <!-- <pre>@{{ $data | json }}</pre> -->

                </div>
            </div>
        </div>
    </template>

    <!-- template for the actionModal component -->
    <script type="x/template" id="tipModal-template">
        <modal :show.sync="show" :on-close="close">
            <!-- <pre>@{{ $data | json }}</pre> -->
            <form action="" v-on:submit.prevent="addTip">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" v-on:click="close()"></button>
                    <h4 class="modal-title"><b>@{{ store.action | capitalize }} Tip</b></h4>
                </div>
                <div class="modal-body">
                    {{ csrf_field() }}
                    <input v-model="tip.id" type="hidden" name="id">

                    <div v-if="store.action != 'delete'" class="form-group">
                        <label class="control-label">Title</label>
                        <input v-model="tip.title" type="text" name="title" class="form-control"
                               placeholder="enter title">
                    </div>
                    <div v-if="store.action != 'delete'" class="form-group">
                        <label class="control-label">Tip</label>
                        <textarea v-model="tip.body" type="text" name="body" rows="4" class="form-control"
                                  placeholder="enter tip"></textarea>
                    </div>

                    <div v-if="store.action == 'delete'" class="form-group text-center">
                        Are you sure you want to delete?
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-dismiss="modal" class="btn btn-default btn-outline" v-on:click="close()">Cancel</button>
                    <button v-if="store.action == 'add'" type="button" class="btn green"
                            v-on:click="addTip(tip)" :disabled="! (tip.title && tip.body)">Create
                    </button>
                    <button v-if="store.action == 'edit'" type="button" class="btn green"
                            v-on:click="updateTip(tip)" :disabled="! (tip.title && tip.body)">Save
                    </button>
                    <button v-if="store.action == 'delete'" type="button" class="btn green"
                            v-on:click="deleteTip(tip)">Delete
                    </button>
                </div>
            </form>
        </modal>
    </script>

    <!-- template for the Modal component -->
    <script type="x/template" id="modal-template">
        <div class="modal-mask" v-on:click="close" v-show="show" transition="modal">
            <div class="modal-container" v-on:click.stop>
                <slot></slot>
            </div>
        </div>
    </script>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>

<!-- Vue -->
<script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-modal-component.js"></script>
<script>
    Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

    var store = {
        action: '',
        active_tip: '',
    };

    Vue.component('app-tips', {
        template: '#tips-template',

        created: function () {
            this.getTips();
            this.getActiveTip();
        },
        data: function () {
            return {
                store: store,
                list: [],
                showTipModal: false,
            };
        },
        events: {
            'addTipEvent': function (tip) {
                this.list.push(tip);
            },
            'delTipEvent': function (tip) {
                this.list.$remove(tip);
            },
        },
        methods: {
            getTips: function () {
                $.getJSON('/safety/tip', function (tips) {
                    this.list = tips;
                }.bind(this));
            },
            getActiveTip: function () {
                this.$http.get('/safety/tip/active')
                        .then(function (response) {
                            this.store.active_tip = response.data.id;
                        }.bind(this)).catch(function (response) {
                    alert('failed getting activetip');
                });

            },
            setTipActive: function (tip) {
                var toggle_tip = {};

                // deactive old tip
                toggle_tip.status = 0;

                this.$http.patch('/safety/tip/' + this.store.active_tip, toggle_tip)
                        .then(function (response) {
                            //toastr.success('Set Tip Active');
                        }.bind(this)).catch(function (response) {
                    alert('failed to deactive  tip');
                });

                //active new tip
                tip.status = 1;
                tip.last_published = moment().format('YYYY-MM-DD HH:mm:ss');
                this.store.active_tip = tip.id;
                this.$http.patch('/safety/tip/' + tip.id, tip)
                        .then(function (response) {
                            toastr.success('Set Tip Active');
                        }.bind(this)).catch(function (response) {
                    alert('failed to activate tip');
                });
            },
        },
    });

    Vue.component('TipModal', {
        template: '#tipModal-template',
        props: ['show', 'action'],
        data: function () {
            var tip = {};
            return {store: store, tip: tip, oTitle: '', oBody: ''};
        },
        events: {
            'add-tip-modal': function () {
                var newtip = {};
                this.oTitle = '';
                this.oBody = '';
                this.tip = newtip;
                this.store.action = 'add';
                this.show = true;
            },
            'edit-tip-modal': function (tip) {
                this.oTitle = tip.title;
                this.oBody = tip.body;
                this.tip = tip;
                this.store.action = 'edit';
                this.show = true;
            },
            'del-tip-modal': function (tip) {
                this.oTitle = tip.title;
                this.oBody = tip.body;
                this.tip = tip;
                this.store.action = 'delete';
                this.show = true;
            }
        },
        methods: {
            close: function () {
                this.show = false;
                this.tip.title = this.oTitle;
                this.tip.body = this.oBody;
            },
            addTip: function (tip) {
                var tipdata = {
                    title: tip.title,
                    body: tip.body,
                    niceDate: moment().format('DD/MM/YY'),
                    user_id: this.store.user_id,
                    fullname: this.store.user_fullname,
                    company_id: this.store.company_id,
                };

                this.$http.post('/safety/tip', tipdata)
                        .then(function (response) {
                            toastr.success('Created new tip ');
                            tipdata.id = response.data.id;
                            this.$dispatch('addTipEvent', tipdata);
                        }.bind(this)).catch(function (response) {
                    alert('failed adding new tip');
                });

                this.close();
            },
            updateTip: function (tip) {
                this.$http.patch('/safety/tip/' + tip.id, tip)
                        .then(function (response) {
                            toastr.success('Saved Tip');
                        }.bind(this)).catch(function (response) {
                    alert('failed to save tip');
                });
                this.show = false;
            },
            deleteTip: function (tip) {
                this.$http.delete('/safety/tip/' + tip.id)
                        .then(function (response) {
                            toastr.success('Deleted Tip');
                            this.$dispatch('delTipEvent', tip);
                        }.bind(this)).catch(function (response) {
                    alert('failed to delete tip');
                    console.log(response);
                });
                this.show = false;
            },
        }
    });

    var myApp = new Vue({
        el: 'body',
        data: store,
    });

</script>
@stop

