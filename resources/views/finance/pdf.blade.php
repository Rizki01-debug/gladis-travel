<!DOCTYPE html>
<html>

<head>
    <title>Laporan Keuangan - GLADIS</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }

        h1,
        h2,
        h3,
        h4 {
            margin: 5px 0;
        }

        .text-center {
            text-align: center;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
        }

        .header small {
            color: #777;
        }

        hr {
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        th,
        td {
            padding: 6px;
        }

        .text-right {
            text-align: right;
        }

        .text-success {
            color: green;
        }

        .text-danger {
            color: red;
        }

        .summary {
            margin-top: 20px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }

        .summary h3 {
            margin: 3px 0;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            text-align: right;
        }
    </style>
</head>

<body>

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <h2>LAPORAN KEUANGAN</h2>
        <h3>TRAVEL GLADIS</h3>

        <small>
            Dicetak pada: {{ now()->format('d M Y H:i') }}
        </small>
    </div>

    {{-- ================= PERIODE ================= --}}
    @if ($start && $end)
        <p class="text-center">
            Periode:
            <b>{{ \Carbon\Carbon::parse($start)->format('d M Y') }}</b>
            -
            <b>{{ \Carbon\Carbon::parse($end)->format('d M Y') }}</b>
        </p>
    @endif

    <hr>

    {{-- ================= PEMASUKAN ================= --}}
    <h4>📥 Data Pemasukan</h4>

    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th>ID Booking</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $i => $t)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>#{{ $t->booking_id }}</td>
                    <td class="text-success text-right">
                        Rp {{ number_format($t->amount ?? 0, 0, ',', '.') }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">
                        Tidak ada data pemasukan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ================= PENGELUARAN ================= --}}
    <h4>📤 Data Pengeluaran</h4>

    <table>
        <thead>
            <tr>
                <th width="10%">No</th>
                <th>Judul</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $i => $e)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $e->title }}</td>
                    <td class="text-danger text-right">
                        Rp {{ number_format($e->amount ?? 0, 0, ',', '.') }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($e->expense_date)->format('d M Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">
                        Tidak ada data pengeluaran
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ================= SUMMARY ================= --}}
    <div class="summary">

        <h3>
            Total Pemasukan:
            <span class="text-success">
                Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
            </span>
        </h3>

        <h3>
            Total Pengeluaran:
            <span class="text-danger">
                Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
            </span>
        </h3>

        <h2>
            Saldo:
            <span>
                Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
            </span>
        </h2>

    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        <p>GLADIS Travel System • Generated by System</p>
    </div>

</body>

</html>
