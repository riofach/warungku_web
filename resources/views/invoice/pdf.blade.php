<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: sans-serif;
            font-size: 12pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
            /* Blue-900 */
            padding-bottom: 12px;
        }

        .header img {
            max-height: 50px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 20pt;
            margin: 0 0 4px 0;
            color: #1e40af;
            /* Blue-800 */
        }

        .header h2 {
            font-size: 13pt;
            margin: 0;
            color: #475569;
            /* Slate-600 */
            font-weight: normal;
        }

        /* Info Section */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 6px;
            font-size: 11pt;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            width: 38%;
            color: #334155;
        }

        .info-table .colon {
            width: 3%;
            color: #334155;
        }

        /* Item table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .items-table th {
            background: #eff6ff;
            /* Blue-50 */
            border-bottom: 2px solid #bfdbfe;
            /* Blue-200 */
            padding: 7px 8px;
            text-align: left;
            font-size: 11pt;
            color: #1e3a8a;
        }

        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }

        .items-table td {
            border-bottom: 1px solid #e2e8f0;
            /* Slate-200 */
            padding: 6px 8px;
            font-size: 11pt;
        }

        /* Total */
        .total-section {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .total-section td {
            padding: 5px 8px;
            font-size: 12pt;
        }

        .total-row td {
            font-weight: bold;
            font-size: 14pt;
            border-top: 2px solid #1e3a8a;
            /* Blue-900 */
            color: #1e3a8a;
        }

        .total-section .text-right {
            text-align: right;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 12px;
            background: #dbeafe;
            /* Blue-100 */
            color: #1e40af;
            /* Blue-800 */
            font-size: 10pt;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 28px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
            font-size: 11pt;
            color: #64748b;
        }

        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 16px 0;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    @php
        // Override warung name as requested
        $displayWarungName = 'WarungLuthfan';

        // Prepare logo for DomPDF (base64 is most reliable)
        $logoPath = public_path('logo-warung.png');
        $logoData = '';
        if (file_exists($logoPath)) {
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoContent = file_get_contents($logoPath);
            $logoData = 'data:image/' . $logoType . ';base64,' . base64_encode($logoContent);
        }
    @endphp

    <div class="header">
        @if ($logoData)
            <img src="{{ $logoData }}" alt="Logo WarungLuthfan">
        @endif
        <h1>{{ $displayWarungName }}</h1>
        <h2>Invoice Pesanan</h2>
    </div>

    {{-- Order Information --}}
    <table class="info-table">
        <tr>
            <td class="label">Kode Pesanan</td>
            <td class="colon">:</td>
            <td><strong>{{ $order->code }}</strong></td>
        </tr>
        <tr>
            <td class="label">Tanggal Pesanan</td>
            <td class="colon">:</td>
            <td>{{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Nama Pemesan</td>
            <td class="colon">:</td>
            <td>{{ $order->customer_name }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Pengiriman</td>
            <td class="colon">:</td>
            <td>
                @if ($order->delivery_type === 'delivery')
                    Antar
                    @if ($order->housingBlock)
                        ({{ $order->housingBlock->name }})
                    @endif
                @else
                    Ambil Sendiri (Pickup)
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Metode Pembayaran</td>
            <td class="colon">:</td>
            <td>{{ strtoupper($order->payment_method) }}</td>
        </tr>
        <tr>
            <td class="label">Status Pembayaran</td>
            <td class="colon">:</td>
            <td><span class="status-badge">{{ $order->status_label }}</span></td>
        </tr>
    </table>

    <hr class="divider">

    {{-- Item Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:6%;">No</th>
                <th>Nama Barang</th>
                <th style="width:10%;" class="text-right">Qty</th>
                <th style="width:22%;" class="text-right">Harga Satuan</th>
                <th style="width:22%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->item?->name ?? 'Item dihapus' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total --}}
    <table class="total-section">
        <tr class="total-row">
            <td>Total Pembayaran</td>
            <td class="text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Terima kasih telah berbelanja di {{ $displayWarungName }}!
    </div>

</body>

</html>
