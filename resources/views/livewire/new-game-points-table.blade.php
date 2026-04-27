<div>

    <!-- HEADER -->
    <div class="flex justify-between flex-col md:flex-row md:items-center mb-4">
        <h2 class="text-xl font-bold mb-2 md:mb-0">Game Points</h2>

        <div class="flex flex-col md:flex-row gap-2">
            <button wire:click="openRechargeModal"
                    class="px-4 py-2 bg-indigo-600 text-white rounded">
                Recharge Points
            </button>

            <button wire:click="openRechargeListModal"
                    class="px-4 py-2 bg-blue-600 text-white rounded">
                Recharge List
            </button>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="flex flex-col md:flex-row gap-2 mb-4">
        <select wire:model.live="searchGame" class="border rounded px-2 py-1">
            <option value="">All Games</option>
            @foreach($games as $g)
                <option value="{{ $g->id }}">{{ $g->name }}</option>
            @endforeach
        </select>

        <input type="date" wire:model.live="dateFrom" class="border rounded px-2 py-1"/>
        <input type="date" wire:model.live="dateTo" class="border rounded px-2 py-1"/>
    </div>

    @if(empty($recordsByDate))
        <div class="bg-white p-6 rounded shadow text-center text-gray-500">
            No Records to display
        </div>
    @else

        @foreach($recordsByDate as $date => $rows)
            <div class="bg-white rounded shadow mb-4 overflow-x-auto p-4">

                <h3 class="font-bold mb-2">Date: {{ $date }}</h3>

                <table class="min-w-full table-auto">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Game</th>
                        <th class="p-3 text-right">Starting Points</th>
                        <th class="p-3 text-right">Recharge Points</th>
                        <th class="p-3 text-right">Closing Points</th>
                        <th class="p-3 text-right">Bonus Added Points</th>

                        <th class="p-3 text-right">Used Points</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($rows as $r)
                        @php
                            $row = $r['row'];

                            if($row && $row->updated_by_id) {
                                $type = 'Override';
                            } elseif($row && $row->created_by_id && !$row->updated_by_id) {
                                $type = 'Manual';
                            } else {
                                $type = 'Auto';
                            }
                        @endphp

                        <tr class="border-t">
                            <td class="p-3">
                                {{ $r['game']->name }}
                            </td>
                            <td class="p-3 text-right">
                                {{ number_format($r['starting'], 2) }}
                            </td>
                            <td class="p-3 text-right">
                                {{ number_format($r['recharge'], 2) }}
                            </td>
                            <td class="p-3 text-right">
                                {{ number_format($r['closing'], 2) }}
                            </td>

                            <td class="p-3 text-right">
                                {{ number_format($r['bonus_added_points'] ?? 0, 2) }}
                            </td>




                            <td class="p-3 text-right">
                                {{ number_format($r['used'], 2) }}
                            </td>

                            <td class="p-3">
                            <span class="
                                px-2 py-1 rounded text-xs
                                @if($type === 'Override') bg-red-200
                                @elseif($type === 'Manual') bg-yellow-200
                                @else bg-green-200
                                @endif
                            ">
                                {{ $type }}
                            </span>
                            </td>

                            <td class="p-3 text-right flex gap-2 justify-end">
                                @if($row)
                                    <button wire:click="editRecord({{ $row->id }})"
                                            class="px-3 py-1 bg-blue-200 rounded">
                                        Edit
                                    </button>

                                    <button wire:click="deleteRecord({{ $row->id }})"
                                            class="px-3 py-1 bg-red-600 text-white rounded">
                                        Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        @endforeach

    @endif
    <div class="mt-4">
        {{ $pagination->links() }}
    </div>
    <!-- ADD / EDIT MODAL -->
    @if($editModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-96">
                <h2 class="text-lg font-bold mb-4">{{ $editingId ? 'Edit' : 'Add' }} Game Points Record</h2>
                <div class="space-y-2">
                    <label class="text-xs">Date</label>
                    <input type="date" wire:model="editDate" class="border rounded w-full px-2 py-1" @if($editingId) disabled @endif />

                    <label class="text-xs">Game</label>
                    <select wire:model="editGameId" class="border rounded w-full px-2 py-1" @if($editingId) disabled @endif>
                        <option value="">Select Game</option>
                        @foreach($games as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>

                    <label class="text-xs">Points</label>
                    <input type="number" step="0.01" wire:model="editPoints" class="border rounded w-full px-2 py-1" />
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('editModal', false)" class="px-4 py-2 border rounded bg-gray-700 text-white">Cancel</button>
                    <button wire:click="saveRecord" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>
            </div>
        </div>
    @endif

    <!-- DELETE MODAL -->
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-80 text-center">
                <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this record?</p>
                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                </div>
            </div>
        </div>
    @endif

    @if($rechargeModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-96">
                <h2 class="text-lg font-bold mb-4">Recharge Game Points</h2>

                <label class="text-xs">Date</label>
                <input type="date" wire:model="rechargeDate" class="border rounded w-full px-2 py-1">

                <label class="text-xs mt-2">Game</label>
                <select wire:model="rechargeGameId" class="border rounded w-full px-2 py-1">
                    <option value="">Select Game</option>
                    @foreach($games as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>

                <label class="text-xs mt-2">Recharge Points</label>
                <input type="number" step="0.01" wire:model="rechargeAmount"
                       class="border rounded w-full px-2 py-1">

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('rechargeModal', false)"
                            class="px-4 py-2 border rounded">Cancel</button>
                    <button wire:click="saveRecharge"
                            class="px-4 py-2 bg-green-600 text-white rounded">
                        Save
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if($rechargeListModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

            <div class="bg-white rounded shadow w-11/12 max-w-4xl max-h-[90vh] flex flex-col p-6">
                <h2 class="text-lg font-bold mb-4 shrink-0">Recharge Records</h2>
                <div class="overflow-y-auto max-h-[420px] border rounded">
                    <table class="min-w-full table-auto mb-4">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">Date</th>
                            <th class="p-2 text-left">Game</th>
                            <th class="p-2 text-right">Recharge Amount</th>
                            @if($this->canDelete())
                                <th class="p-2 text-center">Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(\App\Models\GamePoint::where('recharge_points','>',0)->orderBy('date','desc')->get() as $r)
                            <tr class="border-t">
                                <td class="p-2">{{ $r->date }}</td>
                                <td class="p-2">{{ $r->game->name ?? '-' }}</td>
                                <td class="p-2 text-right">{{ number_format($r->recharge_points,2) }}</td>
                                @if($this->canDelete())
                                    <td class="p-2 text-right flex gap-2 justify-end">

                                        <button wire:click="editRecharge({{ $r->id }})"  class="bg-blue-200 text-black px-3 py-1 rounded">Edit</button>

                                        <button wire:click="confirmDeleteRecharge({{ $r->id }})" class="px-3 py-1 bg-red-600 text-white rounded">Delete</button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end mt-4">
                    <button wire:click="$set('rechargeListModal', false)" class="px-4 py-2 border rounded bg-gray-700 text-white">Close</button>
                </div>

                <!-- Edit Recharge Form -->
                @if($editRechargeId)
                    <div class="mt-4 border-t pt-4">
                        <h3 class="font-bold mb-2">Edit Recharge</h3>
                        <input type="number" step="0.01" wire:model="editRechargeAmount" class="border rounded px-2 py-1 w-40" />
                        <button wire:click="saveRechargeEdit" class="px-4 py-2 bg-green-600 text-white rounded ml-2">Save</button>
                        <button wire:click="$set('editRechargeId', null)" class="px-4 py-2 border rounded ml-2">Cancel</button>
                    </div>
                @endif

                <!-- Delete Confirmation -->
                @if($deleteRechargeId)
                    <div class="mt-4 border-t pt-4 text-center">
                        <p>Are you sure you want to delete this recharge amount?</p>
                        <div class="mt-2 flex justify-center gap-2">
                            <button wire:click="$set('deleteRechargeId', null)" class="px-4 py-2 border rounded">Cancel</button>
                            <button wire:click="deleteRecharge" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif

</div>
