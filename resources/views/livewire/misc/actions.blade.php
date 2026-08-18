<div>
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <h3>Notes
                    <button type="button" wire:click="add" class="btn btn-circle green btn-outline btn-sm pull-right">Add</button>
                </h3>

                @if ($actions->isNotEmpty())
                    <table class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:10%">Date</th>
                            <th>Action</th>
                            <th style="width:20%">Name</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($actions as $action)
                            <tr wire:key="action-{{ $action->id }}">
                                <td>{{ $action->created_at->format('d/m/y') }}</td>
                                <td>{{ $action->action }}</td>
                                <td>{{ $action->user?->fullname ?? $action->user?->full_name ?? 'Unknown' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif

            </div>
        </div>
    </div>


    {{-- Add Note Modal --}}
    @if ($showModal)
        <div class="modal fade in" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.5); z-index: 10050;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" wire:click="close"><span>&times;</span></button>

                        <h4 class="modal-title">Add Note</h4>
                    </div>

                    <div class="modal-body">
                        <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                            <label class="control-label">Description</label>
                            <textarea wire:model="note" rows="4" class="form-control" placeholder="enter note description"></textarea>

                            @error('note')
                            <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn dark btn-outline" wire:click="close">Cancel</button>
                        <button type="button" class="btn green" wire:click="save" wire:loading.attr="disabled" wire:target="save">Create</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>