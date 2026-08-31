<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $purchaseRequest->no_request }} — Purchase Request</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, 'Segoe UI', Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 32px;
            font-size: 13px;
        }
        .toolbar {
            max-width: 900px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .toolbar button {
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #374151;
            cursor: pointer;
        }
        .toolbar button.primary {
            background: #4f46e5;
            border-color: #4f46e5;
            color: #fff;
        }
        .sheet {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px;
        }
        .head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .head h1 { font-size: 20px; margin: 0 0 2px; }
        .head p { margin: 0; color: #6b7280; font-size: 12px; }
        .head .doc-title { text-align: right; }
        .head .doc-title span { font-size: 12px; color: #6b7280; }
        .head .doc-title strong { display: block; font-size: 16px; }
        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px 24px;
            margin-bottom: 20px;
        }
        .meta div label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        .meta div strong { font-size: 13px; }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.items th, table.items td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            font-size: 11.5px;
            text-align: left;
            vertical-align: top;
        }
        table.items th {
            background: #f3f4f6;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: .03em;
            color: #4b5563;
        }
        table.items td.num { text-align: center; width: 32px; }
        table.items td.qty { text-align: center; width: 55px; }
        table.items td.unit { width: 60px; }
        table.items td.price, table.items td.total { text-align: right; width: 95px; }
        .summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }
        .summary table { border-collapse: collapse; width: 260px; font-size: 12px; }
        .summary table td { padding: 4px 6px; }
        .summary table td:last-child { text-align: right; }
        .summary table tr.total td { border-top: 1px solid #9ca3af; font-weight: 700; padding-top: 6px; }
        .note-box {
            margin-bottom: 28px;
            font-size: 12px;
        }
        .note-box label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .signatures {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px 14px;
            margin-top: 32px;
        }
        .signatures .box {
            text-align: center;
        }
        .signatures .box p.role {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin: 0 0 4px;
        }
        .signatures .box .sig-space {
            height: 56px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .signatures .box .sig-space img {
            max-height: 52px;
            max-width: 100%;
        }
        .signatures .box p.name {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            border-top: 1px solid #9ca3af;
            padding-top: 5px;
        }
        .signatures .box p.date {
            margin: 2px 0 0;
            font-size: 10px;
            color: #6b7280;
        }
        .footer-note {
            margin-top: 24px;
            font-size: 10px;
            color: #9ca3af;
        }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
            .sheet { border: none; border-radius: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.close()">Close</button>
        <button type="button" class="primary" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="sheet">
        <div class="head">
            <div>
                <h1>{{ config('app.name', 'e-Procure') }}</h1>
                <p>Purchase Request</p>
            </div>
            <div class="doc-title">
                <span>Purchase Request</span>
                <strong>{{ $purchaseRequest->no_request }}</strong>
            </div>
        </div>

        <div class="meta">
            <div><label>Request No</label><strong>{{ $purchaseRequest->no_request }}</strong></div>
            <div><label>Date</label><strong>{{ $purchaseRequest->date?->format('d-m-Y') ?? '-' }}</strong></div>
            <div><label>Title</label><strong>{{ $purchaseRequest->title }}</strong></div>
            <div><label>Job Location</label><strong>{{ $purchaseRequest->job_location ?? '-' }}</strong></div>
            <div><label>Client</label><strong>{{ $purchaseRequest->client ?? '-' }}</strong></div>
            <div><label>Job No</label><strong>{{ $purchaseRequest->job_no ?? '-' }}</strong></div>
            <div><label>Location Project</label><strong>{{ $purchaseRequest->location_project ?? '-' }}</strong></div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th class="num">No</th>
                    <th>Description</th>
                    <th>Line Item</th>
                    <th class="qty">Qty</th>
                    <th class="unit">Unit</th>
                    <th class="price">Price</th>
                    <th class="total">Total Price</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseRequest->items as $item)
                    <tr>
                        <td class="num">{{ $item->line_no }}</td>
                        <td>{{ $item->description }}</td>
                        <td>
                            {{ $item->line_table ?? '-' }}
                            @if($item->line_sub_table)
                                <br><span style="color:#9ca3af;">{{ $item->line_sub_table }}</span>
                            @endif
                        </td>
                        <td class="qty">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}</td>
                        <td class="unit">{{ $item->unit }}</td>
                        <td class="price">{{ number_format((float) $item->price, 0, ',', '.') }}</td>
                        <td class="total">{{ number_format((float) $item->total_price, 0, ',', '.') }}</td>
                        <td>{{ $item->remarks ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td>Rp {{ number_format((float) $purchaseRequest->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($purchaseRequest->use_ppn)
                    <tr>
                        <td>PPN {{ rtrim(rtrim(number_format((float) $purchaseRequest->ppn_percent, 2, '.', ''), '0'), '.') }}%</td>
                        <td>Rp {{ number_format((float) $purchaseRequest->ppn_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td>Grand Total</td>
                    <td>Rp {{ number_format((float) $purchaseRequest->grand_total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        @if($purchaseRequest->note)
            <div class="note-box">
                <label>Note</label>
                {{ $purchaseRequest->note }}
            </div>
        @endif

        <div class="signatures">
            @foreach($purchaseRequest->approvals as $approval)
                <div class="box">
                    <p class="role">{{ $approval->stage_label }}<br>{{ $approval->role_label }}</p>
                    <div class="sig-space">
                        @if($approval->isSigned())
                            <img src="{{ $approval->signature }}" alt="signature">
                        @endif
                    </div>
                    <p class="name">{{ $approval->signer->name ?? '\u00A0' }}</p>
                    <p class="date">{{ $approval->signed_at?->format('d-m-Y') ?? 'Pending' }}</p>
                </div>
            @endforeach
        </div>

        <p class="footer-note">Generated from {{ config('app.name', 'e-Procure') }} Purchase Request Management on {{ now()->format('d-m-Y H:i') }}.</p>
    </div>
</body>
</html>
