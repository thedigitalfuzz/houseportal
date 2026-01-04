<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HouseSupport Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin-bottom: 10px; }
        h3 { margin-top: 20px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .net-positive { color: green; font-weight: bold; }
        .net-negative { color: red; font-weight: bold; }
        .summary-card { display: inline-block; width: 23%; margin-right: 1%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; }
    </style>
</head>
<body>

@foreach($pdfData as $i => $chunk)
    <h1>HouseSupport Report</h1>
    <h2>{{ $i+1 }}. {{ $chunk['title'] }}</h2>

    {{-- Summary --}}
    <div style="margin-bottom:10px;">
        <div class="summary-card">Transactions: <b>{{ $chunk['summary']['totalTransactions'] }}</b></div>
        <div class="summary-card">Cash In: <b>{{ number_format($chunk['summary']['totalCashin'],2) }}</b></div>
        <div class="summary-card">Cash Out: <b>{{ number_format($chunk['summary']['totalCashout'],2) }}</b></div>
        <div class="summary-card">Net:
            <b class="{{ $chunk['summary']['netAmount'] < 0 ? 'net-negative' : 'net-positive' }}">
                {{ $chunk['summary']['netAmount'] < 0 ? '-$'.number_format(abs($chunk['summary']['netAmount']),2) : '$'.number_format($chunk['summary']['netAmount'],2) }}
            </b>
        </div>
    </div>

    {{-- Top Players --}}
    <h3>Top 5 Cashin Players</h3>
    <table>
        <thead>
        <tr>
            <th>Player</th>
            <th class="text-right">Total Cashin</th>
        </tr>
        </thead>
        <tbody>
        @foreach($chunk['summary']['topCashinPlayers'] as $p)
            <tr>
                <td>{{ $p->player_name }}</td>
                <td class="text-right">{{ number_format($p->total,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h3>Top 5 Cashout Players</h3>
    <table>
        <thead>
        <tr>
            <th>Player</th>
            <th class="text-right">Total Cashout</th>
        </tr>
        </thead>
        <tbody>
        @foreach($chunk['summary']['topCashoutPlayers'] as $p)
            <tr>
                <td>{{ $p->player_name }}</td>
                <td class="text-right">{{ number_format($p->total,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Games & Wallets --}}
    <h3>Games & Wallets</h3>
    <table>
        <thead>
        <tr>
            <th>Category</th>
            <th>Item</th>
            <th class="text-right">Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Game with Most Cashin</td>
            <td>{{ $chunk['summary']['topCashinGame']->name ?? '-' }}</td>
            <td class="text-right">{{ number_format($chunk['summary']['topCashinGame']->amount ?? 0,2) }}</td>
        </tr>
        <tr>
            <td>Game with Most Cashout</td>
            <td>{{ $chunk['summary']['topCashoutGame']->name ?? '-' }}</td>
            <td class="text-right">{{ number_format($chunk['summary']['topCashoutGame']->amount ?? 0,2) }}</td>
        </tr>
        <tr>
            <td>Wallet with Most Cashin</td>
            <td>{{ $chunk['summary']['topCashinWallet'] ? $chunk['summary']['topCashinWallet']->agent . ' | ' . $chunk['summary']['topCashinWallet']->wallet_name : '-' }}</td>
            <td class="text-right">{{ number_format($chunk['summary']['topCashinWallet']->amount ?? 0,2) }}</td>
        </tr>
        <tr>
            <td>Wallet with Most Cashout</td>
            <td>{{ $chunk['summary']['topCashoutWallet'] ? $chunk['summary']['topCashoutWallet']->agent . ' | ' . $chunk['summary']['topCashoutWallet']->wallet_name : '-' }}</td>
            <td class="text-right">{{ number_format($chunk['summary']['topCashoutWallet']->amount ?? 0,2) }}</td>
        </tr>
        </tbody>
    </table>

    {{-- Wallet Summary --}}
    <h3>Wallet Summary</h3>
    <table>
        <thead>
        <tr>
            <th>Wallet</th>
            <th class="text-right">Cashin</th>
            <th class="text-right">Cashout</th>
        </tr>
        </thead>
        <tbody>
        @foreach($chunk['summary']['walletSummary'] as $w)
            <tr>
                <td>{{ $w->agent }} | {{ $w->wallet_name }} | {{ $w->wallet_remarks }}</td>
                <td class="text-right">{{ number_format($w->cashin,2) }}</td>
                <td class="text-right">{{ number_format($w->cashout,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{-- Staff Performance --}}
    <h3>Top 3 Staff Performance</h3>
    <table>
        <thead>
        <tr>
            <th>Staff</th>
            <th class="text-right">Transactions</th>
            <th class="text-right">Cashin</th>
            <th class="text-right">Cashout</th>
            <th class="text-right">Net</th>
        </tr>
        </thead>
        <tbody>
        @foreach($chunk['summary']['topStaffs'] as $s)
            <tr>
                <td>{{ $s->staff_name }}</td>
                <td class="text-right">{{ $s->transactions }}</td>
                <td class="text-right">{{ number_format($s->cashin,2) }}</td>
                <td class="text-right">{{ number_format($s->cashout,2) }}</td>
                <td class="text-right {{ $s->net < 0 ? 'net-negative' : 'net-positive' }}">
                    {{ $s->net < 0 ? '-$'.number_format(abs($s->net),2) : '$'.number_format($s->net,2) }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div style="page-break-after:always;"></div>
@endforeach

</body>
</html>
