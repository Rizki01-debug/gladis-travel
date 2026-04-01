<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h2, h3, h4 {
            margin: 5px 0;
        }

        .text-center {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th {
            background-color: #f2f2f2;
        }

        th, td {
            padding: 6px;
            text-align: left;
        }

        .summary {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <h2 class="text-center">LAPORAN KEUANGAN TRAVEL GLADIS</h2>

    @if($start && $end)
        <p class="text-center">
            Periode: {{ \Carbon\Carbon::parse($start)->format('d-m-Y') }}
            s/d
            {{ \Carbon\Carbon::parse($end)->format('d-m-Y') }}
        </p>
    @endif

    <hr>

    <!-- ================= PEMASUKAN ================= -->
    <h4>Pemasukan</h4>

    <table>
        <thead>
            <tr>
                <th>ID Booking</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td>#{{ $t->booking_id }}</td>
                    <td>Rp {{ number_format($t->amount) }}</td>
                    <td>{{ $t->created_at->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">
                        Tidak ada data pemasukan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ================= PENGELUARAN ================= -->
    <h4>Pengeluaran</h4>

    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $e)
                <tr>
                    <td>{{ $e->title }}</td>
                    <td>Rp {{ number_format($e->amount) }}</td>
                    <td>{{ \Carbon\Carbon::parse($e->expense_date)->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">
                        Tidak ada data pengeluaran
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ================= SUMMARY ================= -->
    <div class="summary">
        <h3>Total Pemasukan: Rp {{ number_format($totalIncome) }}</h3>
        <h3>Total Pengeluaran: Rp {{ number_format($totalExpense) }}</h3>
        <h2>Saldo: Rp {{ number_format($balance) }}</h2>
    </div>

</body>
</html>