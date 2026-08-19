<div>
    <div class="row">
        <div class="col-md-12">
            <h4 class="clearfix" style="margin-bottom: 5px">
                Notes
                @if ($allowAdd)
                    <button type="button" wire:click="add" class="btn btn-circle green btn-outline btn-sm pull-right">Add</button>
                @endif
            </h4>
            <hr style="padding: 0; margin: 0 0 10px 0">

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
                @else
                    <div class="font-grey-silver">No notes.</div>
                @endif

        </div>
    </div>


    {{-- Add Note Modal --}}
    <x-ui.modal :show="$showModal" title="Add Note" close-action="close">
        <x-form.textarea name="note" label="Description" rows="4" wire:model="note" placeholder="Enter note description"/>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="close">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">Create</button>
        </x-slot>
    </x-ui.modal>
</div>