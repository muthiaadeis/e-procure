<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $rlp->no_rlp }} — RRP</title>
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
            margin-bottom: 20px;
        }
        table.items th, table.items td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            font-size: 11px;
            text-align: left;
            vertical-align: top;
        }
        table.items th {
            background: #f3f4f6;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: .03em;
            color: #4b5563;
        }
        table.items td.num { text-align: center; width: 30px; }
        table.items td.qty { text-align: center; width: 55px; }
        table.items td.unit { width: 60px; }
        table.items td.money { text-align: right; white-space: nowrap; }
        table.items tfoot td { font-weight: 600; background: #f9fafb; }
        .costs-title {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            margin: 24px 0 8px;
        }
        .grand-total {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        .grand-total .box {
            text-align: right;
        }
        .grand-total .box span {
            display: block;
            font-size: 10px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .grand-total .box strong {
            font-size: 15px;
        }
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
            margin: 0 0 4px;
        }
        .signatures .box .sig-space {
            height: 56px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            margin-bottom: 4px;
        }
        .signatures .box .sig-space img {
            max-height: 52px;
            max-width: 100%;
            object-fit: contain;
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
                <p>Local Purchase / RRP Management</p>
            </div>
            <div class="doc-title">
                <span>Request &amp; Revenue Purchase Part</span>
                <strong>{{ $rlp->no_rlp }}</strong>
            </div>
        </div>

        <div class="meta">
            <div>
                <label>No RRP</label>
                <strong>{{ $rlp->no_rlp }}</strong>
            </div>
            <div>
                <label>Prepare Date</label>
                <strong>{{ $rlp->date ? $rlp->date->format('d-m-Y') : '-' }}</strong>
            </div>
            <div>
                <label>Status</label>
                <strong>{{ $rlp->status }}</strong>
            </div>
            <div>
                <label>Prepared By</label>
                <strong>{{ $rlp->creator->name ?? '-' }}</strong>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th class="num">No</th>
                    <th>Description</th>
                    <th class="qty">Qty</th>
                    <th class="unit">UOM</th>
                    <th class="money">Part Catalog (Ext)</th>
                    <th>Selected Vendor</th>
                    <th>Estimasi (Days)</th>
                    <th class="money">Vendor Price (Ext)</th>
                    <th class="money">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rlp->items as $item)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $item->description }}{{ $item->pn ? ' — PN: '.$item->pn : '' }}</td>
                        <td class="qty">{{ $item->qty }}</td>
                        <td class="unit">{{ $item->uom }}</td>
                        <td class="money">Rp {{ number_format($item->part_catalog_ext_price, 0, ',', '.') }}</td>
                        <td>{{ $item->selectedVendor->vendor_name ?? '-' }}</td>
                        <td>{{ $item->selectedVendor->delivery_estimate ? $item->selectedVendor->delivery_estimate.' days' : '-' }}</td>
                        <td class="money">Rp {{ number_format($item->selectedVendor->ext_price ?? 0, 0, ',', '.') }}</td>
                        <td class="money" style="color: {{ $item->revenue_ext_price < 0 ? '#dc2626' : '#059669' }}">
                            Rp {{ number_format($item->revenue_ext_price, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">Total</td>
                    <td class="money">Rp {{ number_format($rlp->part_catalog_ext_price, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                    <td></td>
                    <td class="money" style="color: {{ $rlp->revenue_ext_price < 0 ? '#dc2626' : '#059669' }}">
                        Rp {{ number_format($rlp->revenue_ext_price, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        @if($rlp->costs->isNotEmpty())
            <p class="costs-title">WUR Cost Estimasi</p>
            <table class="items">
                <thead>
                    <tr>
                        <th class="num">No</th>
                        <th>Job Description</th>
                        <th class="qty">Qty</th>
                        <th class="money">U/Price</th>
                        <th class="money">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rlp->costs as $cost)
                        <tr>
                            <td class="num">{{ $loop->iteration }}</td>
                            <td>{{ $cost->job_description }}</td>
                            <td class="qty">{{ rtrim(rtrim(number_format($cost->quantity, 2, ',', '.'), '0'), ',') }}</td>
                            <td class="money">Rp {{ number_format($cost->u_price, 0, ',', '.') }}</td>
                            <td class="money">Rp {{ number_format($cost->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4">WUR Grand Total</td>
                        <td class="money">Rp {{ number_format($rlp->wur_grand_total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="signatures">
            <div class="box">
                <p class="role">Prepared By</p>
                <div class="sig-space">
                    @if($rlp->created_signature)
                        <img src="{{ $rlp->created_signature }}" alt="signature">
                    @endif
                </div>
                <p class="name">{{ $rlp->creator->name ?? '-' }}</p>
                <p class="date">{{ $rlp->created_at ? $rlp->created_at->format('d-m-Y H:i') : '-' }}</p>
            </div>
            <div class="box">
                <p class="role">Review By</p>
                <div class="sig-space">
                    @if($rlp->reviewed_signature)
                        <img src="{{ $rlp->reviewed_signature }}" alt="signature">
                    @endif
                </div>
                <p class="name">{{ $rlp->reviewer->name ?? '-' }}</p>
                <p class="date">{{ $rlp->reviewed_at?->format('d-m-Y H:i') ?? '-' }}</p>
            </div>
            <div class="box">
                <p class="role">Acknowledge By</p>
                <div class="sig-space">
                    @if($rlp->acknowledged_signature)
                        <img src="{{ $rlp->acknowledged_signature }}" alt="signature">
                    @endif
                </div>
                <p class="name">{{ $rlp->acknowledger->name ?? '-' }}</p>
                <p class="date">{{ $rlp->acknowledged_at?->format('d-m-Y H:i') ?? '-' }}</p>
            </div>
            <div class="box">
                <p class="role">Approved By</p>
                <div class="sig-space">
                    @if($rlp->approved_signature)
                        <img src="{{ $rlp->approved_signature }}" alt="signature">
                    @endif
                </div>
                <p class="name">{{ $rlp->approver->name ?? '-' }}</p>
                <p class="date">{{ $rlp->approved_at?->format('d-m-Y H:i') ?? '-' }}</p>
            </div>
        </div>

        <p class="footer-note">Generated from {{ config('app.name', 'e-Procure') }} RRP Management on {{ now()->format('d-m-Y H:i') }}.</p>
    </div>
</body>
</html>
