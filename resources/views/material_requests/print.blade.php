<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $materialRequest->no_mr }} — Material Request</title>
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
            max-width: 780px;
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
            max-width: 780px;
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
            grid-template-columns: 1fr 1fr;
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
            margin-bottom: 28px;
        }
        table.items th, table.items td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            font-size: 12px;
            text-align: left;
            vertical-align: top;
        }
        table.items th {
            background: #f3f4f6;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: .03em;
            color: #4b5563;
        }
        table.items td.num { text-align: center; width: 40px; }
        table.items td.qty { text-align: center; width: 70px; }
        table.items td.unit { width: 80px; }
        .signatures {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 40px;
            text-align: center;
        }
        .signatures .box p.role {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin: 0 0 48px;
        }
        .signatures .box p.name {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            border-top: 1px solid #9ca3af;
            padding-top: 6px;
        }
        .signatures .box p.date {
            margin: 2px 0 0;
            font-size: 11px;
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
                <p>Material Request Management</p>
            </div>
            <div class="doc-title">
                <span>Material Request</span>
                <strong>{{ $materialRequest->no_mr }}</strong>
            </div>
        </div>

        <div class="meta">
            <div>
                <label>No MR</label>
                <strong>{{ $materialRequest->no_mr }}</strong>
            </div>
            <div>
                <label>Date</label>
                <strong>{{ $materialRequest->date->format('d-m-Y') }}</strong>
            </div>
            <div>
                <label>Charge To</label>
                <strong>{{ $materialRequest->charge_to }}</strong>
            </div>
            <div>
                <label>Status</label>
                <strong>{{ $materialRequest->status }}</strong>
            </div>
            <div>
                <label>Requested By</label>
                <strong>{{ $materialRequest->creator->name ?? '-' }}</strong>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th class="num">No</th>
                    <th>Material Description</th>
                    <th class="qty">Qty</th>
                    <th class="unit">Unit</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materialRequest->items as $item)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="qty">{{ $item->quantity }}</td>
                        <td class="unit">{{ $item->unit }}</td>
                        <td>{{ $item->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signatures">
            <div class="box">
                <p class="role">Requested By</p>
                <p class="name">{{ $materialRequest->creator->name ?? '-' }}</p>
                <p class="date">{{ $materialRequest->date->format('d-m-Y') }}</p>
            </div>
            <div class="box">
                <p class="role">Approval 1</p>
                <p class="name">{{ $materialRequest->approverA->name ?? '-' }}</p>
                <p class="date">{{ $materialRequest->approved_a_at?->format('d-m-Y') ?? '-' }}</p>
            </div>
            <div class="box">
                <p class="role">Approval 2</p>
                <p class="name">{{ $materialRequest->approverC->name ?? '-' }}</p>
                <p class="date">{{ $materialRequest->approved_c_at?->format('d-m-Y') ?? '-' }}</p>
            </div>
            {{-- BARU: kolom Finance --}}
            <div class="box">
                <p class="role">Finance</p>
                @if($materialRequest->is_rejected_by_finance)
                    <p class="name">{{ $materialRequest->financeRejector->name ?? '-' }}</p>
                    <p class="date">Rejected · {{ $materialRequest->finance_rejected_at?->format('d-m-Y') ?? '-' }}</p>
                @elseif($materialRequest->paid_at)
                    <p class="name">{{ $materialRequest->paidByUser->name ?? '-' }}</p>
                    <p class="date">{{ $materialRequest->paid_at->format('d-m-Y') }}</p>
                @else
                    <p class="name">&nbsp;</p>
                    <p class="date">Pending</p>
                @endif
            </div>
        </div>

        <p class="footer-note">Generated from {{ config('app.name', 'e-Procure') }} Material Request Management on {{ now()->format('d-m-Y H:i') }}.</p>
        </div>
    </body>
</html>
