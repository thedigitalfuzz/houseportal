<div class="p-4">
    <div class="flex items-start md:items-center justify-between flex-col md:flex-row mb-4 gap-2">
        <h1 class="text-3xl font-bold">Wallet Details</h1>
        <a href="{{ route('wallets') }}"
           class="px-4 py-2 bg-gray-700 text-white rounded">
            Back
        </a>
    </div>

    <div class="grid grid-cols-1 mb-6">
    <div class="bg-white shadow rounded p-4 overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
            <tr class="bg-gray-100">
                <th class="p-3 text-left">Agent</th>
                <th class="p-3 text-left">Wallet Name</th>
                <th class="p-3 text-left">Wallet Remarks</th>
                <th class="p-3 text-left">Created At</th>
            </tr>
            </thead>

            <tbody>
            @foreach($walletDetails as $wd)
                <tr class="border-t">
                    <td class="p-3">{{ $wd->agent }}</td>
                    <td class="p-3">{{ $wd->wallet_name }}</td>
                    <td class="p-3">{{ $wd->wallet_remarks ?? '-' }}</td>
                    <td class="p-2">{{ $wd->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    </div>

</div>
