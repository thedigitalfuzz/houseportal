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
        .summary-card {width: 32%; margin-right: 1%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; }

    </style>
</head>
<body>

@foreach($pdfData as $i => $chunk)
    <h1>HouseSupport Report</h1>
    <h2> {{ $chunk['title'] }}</h2>

    {{-- Summary --}}
    <div style="margin-bottom:10px;">
        <div style="width:100%; margin-bottom:10px;">
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Transactions: <b>{{ $chunk['summary']['totalTransactions'] }}</b>
            </div>
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Cashin Transactions: <b>{{ $chunk['summary']['totalCashinTransactions'] }}</b>
            </div>
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top;">
                Cashout Transactions: <b>{{ $chunk['summary']['totalCashoutTransactions'] }}</b>
            </div>
        </div>

        <div style="width:100%; margin-bottom:10px;">
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Cashin Amount: <b>${{ number_format($chunk['summary']['totalCashin'],2) }}</b>
            </div>
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Cashout Amount: <b>${{ number_format($chunk['summary']['totalCashout'],2) }}</b>
            </div>
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top;">
                Net Amount: <b class="{{ $chunk['summary']['netAmount'] < 0 ? 'net-negative' : 'net-positive' }}">
                    {{ $chunk['summary']['netAmount'] < 0 ? '-$'.number_format(abs($chunk['summary']['netAmount']),2) : '$'.number_format($chunk['summary']['netAmount'],2) }}
                </b>
            </div>
        </div>

        <div style="width:100%; margin-bottom:10px;">
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top;">
                Total Players:<br> <b>{{ $chunk['summary']['totalPlayers'] }}</b>
            </div>
            @if($chunk['summary']['falseTransactionCount'] > 0)
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                False Transactions: <b>{{ $chunk['summary']['falseTransactionCount'] }}</b>
            </div>
            <div style="display:inline-block; width:25%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Players with False Transactions:<br> <b>{{ $chunk['summary']['falseTransactionPlayers']->implode(', ') }}</b>
            </div>
            @endif


        </div>
        <div style="width:100%; margin-bottom:10px;">
            <div style="display:inline-block; width:30%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Top Player (Most Transactions):<br> <b>{{ $chunk['summary']['topTransactionPlayer']->player_name ?? '-' }} ({{ $chunk['summary']['topTransactionPlayer']->total_transactions ?? 0 }})</b>
            </div>
            <div style="display:inline-block; width:30%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Top Player (Most Cashin):<br> <b>{{ $chunk['summary']['topCashinPlayer']->player_name ?? '-' }} ({{ $chunk['summary']['topCashinPlayer']->total ?? 0 }})</b>
            </div>

        </div>
        <div style="width:100%; margin-bottom:10px;">
            <div style="display:inline-block; width:30%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Total Game Points Used:<br> <b> {{ number_format($chunk['summary']['gamePointsPerformance']['totals']['used_points'],2) }}</b>
            </div>
            <div style="display:inline-block; width:30%; padding:6px; border:1px solid #ccc; border-radius:4px; background-color:#f9f9f9; vertical-align:top; margin-right:1%;">
                Top Game by Points Used:<br> <b> {{ $chunk['summary']['gamePointsPerformance']['totals']['topGamePointsUsed'] ?? '-' }}</b>
            </div>
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
                <td class="text-right">${{ number_format($p->total,2) }}</td>
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
                <td class="text-right">${{ number_format($p->total,2) }}</td>
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
            <th class="text-right">Amount / Transactions</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Game with Most Cashin</td>
            <td>{{ $chunk['summary']['topCashinGame']->name ?? '-' }}</td>
            <td class="text-right">${{ number_format($chunk['summary']['topCashinGame']->amount ?? 0,2) }}</td>
        </tr>
        <tr>
            <td>Game with Most Cashout</td>
            <td>{{ $chunk['summary']['topCashoutGame']->name ?? '-' }}</td>
            <td class="text-right">${{ number_format($chunk['summary']['topCashoutGame']->amount ?? 0,2) }}</td>
        </tr>
        <tr>
            <td>Wallet with Most Transactions</td>
            <td>{{ $chunk['summary']['topTransactionWallet'] ? $chunk['summary']['topTransactionWallet']->agent . ' | ' . $chunk['summary']['topTransactionWallet']->wallet_name . ' | ' . $chunk['summary']['topTransactionWallet']->wallet_remarks : '-' }}</td>
            <td class="text-right">{{ $chunk['summary']['topTransactionWallet']->transactions ?? 0 }}</td>
        </tr>
        <tr>
            <td>Wallet with Most Cashin</td>
            <td>{{ $chunk['summary']['topCashinWallet'] ? $chunk['summary']['topCashinWallet']->agent . ' | ' . $chunk['summary']['topCashinWallet']->wallet_name . ' | ' . $chunk['summary']['topCashinWallet']->wallet_remarks : '-' }}</td>
            <td class="text-right">${{ number_format($chunk['summary']['topCashinWallet']->amount ?? 0,2) }}</td>
        </tr>
        <tr>
            <td>Wallet with Most Cashout</td>
            <td>{{ $chunk['summary']['topCashoutWallet'] ? $chunk['summary']['topCashoutWallet']->agent . ' | ' . $chunk['summary']['topCashoutWallet']->wallet_name . ' | ' . $chunk['summary']['topCashoutWallet']->wallet_remarks : '-' }}</td>
            <td class="text-right">${{ number_format($chunk['summary']['topCashoutWallet']->amount ?? 0,2) }}</td>
        </tr>
        </tbody>
    </table>

    {{-- Games Summary --}}
    <h3>Game Points and Performance Summary</h3>
    <table>
        <thead>
        <tr>
            <th>Game</th>
            <th>Starting Points</th>
            <th>Closing Points</th>
            <th>Used Points</th>
            <th>Cash In</th>
            <th>Cash Out</th>
            <th>Net</th>
            <th>Top Player</th>
        </tr>
        </thead>
        <tbody>
        @foreach($chunk['summary']['gamePointsPerformance']['data'] as $g)
            <tr>
                <td>{{ $g['game_name'] }}</td>
                <td class="text-right">{{ number_format($g['total_starting_points'],2) }}</td>
                <td class="text-right">{{ number_format($g['points'],2) }}</td>
                <td class="text-right">{{ number_format($g['used_points'],2) }}</td>
                <td class="text-right">${{ number_format($g['total_cashin'],2) }}</td>
                <td class="text-right">${{ number_format($g['total_cashout'],2) }}</td>
                <td class="text-right {{ $g['total_net'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $g['total_net'] < 0 ? '-$'.number_format(abs($g['total_net']),2) : '$'.number_format($g['total_net'],2) }}
                </td>
                <td class="p-2">{{ $g['top_player'] }}</td>
            </tr>
        @endforeach
        <tr style="font-weight:bold">
            <td class="text-right">TOTAL</td>
            <td class="text-right">{{ number_format($chunk['summary']['gamePointsPerformance']['totals']['total_starting_points'],2) }}</td>
            <td class="text-right">{{ number_format($chunk['summary']['gamePointsPerformance']['totals']['total_closing_points'],2) }}</td>
            <td class="text-right">{{ number_format($chunk['summary']['gamePointsPerformance']['totals']['used_points'],2) }}</td>
           <td class="text-right">${{ number_format($chunk['summary']['gamePointsPerformance']['totals']['total_cashin'],2) }}</td>
            <td class="text-right">${{ number_format($chunk['summary']['gamePointsPerformance']['totals']['total_cashout'],2) }}</td>
            <td class="text-right {{ $chunk['summary']['gamePointsPerformance']['totals']['total_net'] < 0 ? 'net-negative' : 'net-positive' }}">
                {{ $chunk['summary']['gamePointsPerformance']['totals']['total_net'] < 0
                    ? '-$'.number_format(abs($chunk['summary']['gamePointsPerformance']['totals']['total_net']),2)
                    : '$'.number_format($chunk['summary']['gamePointsPerformance']['totals']['total_net'],2)
                }}
            </td>
            <td></td>
        </tr>
        </tbody>
    </table>

    {{-- Wallet Summary --}}
    <h3>Wallet Summary</h3>
    <table>
        <thead>
        <tr>
            <th>Wallet</th>
            <th class="text-right">Transactions</th>
            <th class="text-right">Cashin</th>
            <th class="text-right">Cashout</th>
            <th class="text-right">Net</th>
        </tr>
        </thead>
        <tbody>
        @php $tw = $tc = $to = $tn = 0; @endphp
        @foreach($chunk['summary']['walletSummary'] as $w)
            <tr>
                <td>{{ $w->agent }} | {{ $w->wallet_name }} | {{ $w->wallet_remarks }}</td>
                <td class="text-right">{{ $w->transactions }}</td>
                <td class="text-right">${{ number_format($w->cashin,2) }}</td>
                <td class="text-right">${{ number_format($w->cashout,2) }}</td>
                <td class="text-right {{ $w->net < 0 ? 'net-negative' : 'net-positive' }}">
                    {{ $w->net < 0 ? '-$'.number_format(abs($w->net),2) : '$'.number_format($w->net,2) }}
                </td>
            </tr>
            @php
                $tw += $w->transactions;
                $tc += $w->cashin;
                $to += $w->cashout;
                $tn += $w->net;
            @endphp
        @endforeach
        <tr style="font-weight:bold">
            <td>Total</td>
            <td class="text-right">{{ $tw }}</td>
            <td class="text-right">${{ number_format($tc,2) }}</td>
            <td class="text-right">${{ number_format($to,2) }}</td>
            <td class="text-right {{ $tn < 0 ? 'net-negative' : 'net-positive' }}">
                {{ $tn < 0 ? '-$'.number_format(abs($tn),2) : '$'.number_format($tn,2) }}
            </td>
        </tr>
    </table>

    {{-- Agent Performance --}}
    <h3>Overall Agent Performance</h3>
    <table>
        <thead>
        <tr>
            <th>Agent</th>
            <th class="text-right">Transactions</th>
            <th class="text-right">Cashin</th>
            <th class="text-right">Cashout</th>
            <th class="text-right">Net</th>
        </tr>
        </thead>
        <tbody>
        @php $st = $sc = $so = $sn = 0; @endphp
        @foreach($chunk['summary']['topStaffs'] as $s)
            <tr>
                <td>{{ $s->staff_name }}</td>
                <td class="text-right">{{ $s->transactions }}</td>
                <td class="text-right">${{ number_format($s->cashin,2) }}</td>
                <td class="text-right">${{ number_format($s->cashout,2) }}</td>
                <td class="text-right {{ $s->net < 0 ? 'net-negative' : 'net-positive' }}">
                    {{ $s->net < 0 ? '-$'.number_format(abs($s->net),2) : '$'.number_format($s->net,2) }}
                </td>
            </tr>
            @php
                $st += $s->transactions;
                $sc += $s->cashin;
                $so += $s->cashout;
                $sn += $s->net;
            @endphp
        @endforeach
        <tr style="font-weight:bold">
            <td>Total</td>
            <td class="text-right">{{ $st }}</td>
            <td class="text-right">${{ number_format($sc,2) }}</td>
            <td class="text-right">${{ number_format($so,2) }}</td>
            <td class="text-right {{ $sn < 0 ? 'net-negative' : 'net-positive' }}">
                {{ $sn < 0 ? '-$'.number_format(abs($sn),2) : '$'.number_format($sn,2) }}
            </td>
        </tr>
        </tbody>
    </table>

    <div style="page-break-after:always;"></div>
@endforeach

</body>
</html>
