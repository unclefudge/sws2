<x-ui.modal-styles/>

<!-- template for the actionModal component -->
<script type="x/template" id="actionModal-template">
    <modal :show.sync="show" :on-close="close">
        <!-- <pre>@{{ $data | json }}</pre> -->
        <form action="" v-on:submit.prevent="addAction">
            <div class="sws-modal-header">
                <h3 class="sws-modal-title">@{{ xx.action | capitalize }} Note</h3>
                <button type="button" class="sws-modal-close" @click="close()" aria-label="Close">&times;</button>
            </div>

            <div class="sws-modal-body">
                {{ csrf_field() }}
                <input v-model="action.id" type="hidden" name="id">

                <div class="form-group">
                    <label class="control-label">Description</label>
                    <textarea v-model="action.action" type="text" name="action" rows="4" class="form-control" placeholder="Enter note description"></textarea>
                </div>
            </div>

            <div class="sws-modal-footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" @click="close()">Cancel</button>
                <button v-if="xx.action == 'add'" type="button" class="sws-modal-btn sws-modal-btn-primary" @click="addAction(action)" :disabled="! action.action">Create</button>
                <button v-else type="button" class="sws-modal-btn sws-modal-btn-primary" @click="updateAction(action)" :disabled="! action.action">Save</button>
            </div>
        </form>
    </modal>
</script>

<!-- template for the Modal component -->
<script type="x/template" id="modal-template">
    <div class="sws-modal-backdrop" @click="close" v-show="show" transition="modal">
        <div class="sws-modal-card" @click.stop style="max-width:560px">
            <slot></slot>
        </div>
    </div>
</script>
