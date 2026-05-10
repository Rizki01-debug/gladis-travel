<!DOCTYPE html>
<html>

<head>
    <title>Laporan Keuangan - GLADIS</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #2d3748;
            margin: 30px;
        }

        /* ================= HEADER ================= */
        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 1px;
        }

        .header h3 {
            margin: 2px 0;
            font-size: 14px;
            font-weight: normal;
        }

        .header small {
            color: #718096;
        }

        .divider {
            border-bottom: 2px solid #e2e8f0;
            margin: 10px 0 15px;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #edf2f7;
            color: #2d3748;
            font-weight: 600;
            text-align: center;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #e2e8f0;
        }

        td {
            font-size: 11px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-success {
            color: #16a34a;
            font-weight: bold;
        }

        .text-danger {
            color: #dc2626;
            font-weight: bold;
        }

        /* ================= SECTION TITLE ================= */
        .section-title {
            font-weight: bold;
            margin: 15px 0 5px;
            font-size: 13px;
        }

        /* ================= SUMMARY ================= */
        .summary {
            margin-top: 20px;
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
        }

        .summary p {
            margin: 4px 0;
            font-size: 12px;
        }

        .summary .balance {
            font-size: 16px;
            font-weight: bold;
        }

        /* ================= FOOTER ================= */
        .footer {
            margin-top: 40px;
            font-size: 10px;
            text-align: right;
            color: #94a3b8;
        }
    </style>
</head>

<body>

    {{-- ================= HEADER ================= --}}
    <div class="header">
        <h2>LAPORAN KEUANGAN</h2>
        <h3>TRAVEL GLADIS</h3>
        <small>Dicetak pada: {{ now()->format('d M Y H:i') }}</small>
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

    <div class="divider"></div>

    {{-- ================= PEMASUKAN ================= --}}
    <div class="section-title">Data Pemasukan</div>

    <table>
        <thead>
            <tr>
                <th width="8%">No</th>
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
                    <td class="text-center">
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
    <div class="section-title">Data Pengeluaran</div>

    <table>
        <thead>
            <tr>
                <th width="8%">No</th>
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
                    <td class="text-center">
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

        <p>
            Total Pemasukan:
            <span class="text-success">
                Rp {{ number_format($totalIncome ?? 0, 0, ',', '.') }}
            </span>
        </p>

        <p>
            Total Pengeluaran:
            <span class="text-danger">
                Rp {{ number_format($totalExpense ?? 0, 0, ',', '.') }}
            </span>
        </p>

        <p class="balance">
            Saldo:
            Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
        </p>

    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="footer">
        GLADIS Travel System • Generated by System
    </div>

</body>

</html>