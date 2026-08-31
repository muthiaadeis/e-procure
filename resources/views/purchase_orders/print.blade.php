<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $purchaseOrder->po_no }} — Purchase Order</title>
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

        .supplier-order {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }
        .supplier-order div label {
            display: block;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        .supplier-order div strong { font-size: 12.5px; }

        .addr-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 18px;
        }
        .addr-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .addr-box label {
            display: block;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9ca3af;
            margin-bottom: 3px;
        }
        .addr-box p { margin: 0 0 8px; font-size: 12px; white-space: pre-line; }
        .addr-box p:last-child { margin-bottom: 0; }

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
        table.items td { white-space: pre-line; }
        table.items td.num { text-align: center; width: 32px; }
        table.items td.qty { text-align: center; width: 50px; }
        table.items td.uom { width: 48px; }
        table.items td.brand { width: 90px; }
        table.items td.price, table.items td.total { text-align: right; width: 105px; }

        .summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 24px;
        }
        .summary table { border-collapse: collapse; width: 260px; font-size: 12px; }
        .summary table td { padding: 4px 6px; }
        .summary table td:last-child { text-align: right; }
        .summary table tr.total td { border-top: 1px solid #9ca3af; font-weight: 700; padding-top: 6px; }

        .signatures {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px 14px;
            margin-top: 32px;
        }
        .signatures .box { text-align: center; }
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
        .signatures .box .sig-space img { max-height: 52px; max-width: 100%; }
        .signatures .box p.name {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            border-top: 1px solid #9ca3af;
            padding-top: 5px;
        }
        .signatures .box p.date { margin: 2px 0 0; font-size: 10px; color: #6b7280; }

        .vendor-acceptance {
            margin-top: 32px;
            border-top: 1px dashed #9ca3af;
            padding-top: 16px;
        }
        .vendor-acceptance h3 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #4b5563;
            margin: 0 0 12px;
        }
        .vendor-acceptance .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .vendor-acceptance .box .sig-space {
            height: 60px;
            border-bottom: 1px solid #9ca3af;
            margin-bottom: 6px;
        }
        .vendor-acceptance .box label {
            display: block;
            font-size: 10px;
            color: #6b7280;
        }

        .footer-note { margin-top: 24px; font-size: 10px; color: #9ca3af; }

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
                <p>Purchase Order</p>
            </div>
            <div class="doc-title">
                <span>Purchase Order No</span>
                <strong>{{ $purchaseOrder->po_no }}</strong>
            </div>
        </div>

        <div class="supplier-order">
            <div><label>Our Reference</label><strong>{{ $purchaseOrder->our_reference ?? '-' }}</strong></div>
            <div><label>Supplier No</label><strong>{{ $purchaseOrder->supplier_no ?? '-' }}</strong></div>
            <div><label>Our Order Date</label><strong>{{ $purchaseOrder->our_order_date?->format('d-m-Y') ?? '-' }}</strong></div>
            <div><label>Revision</label><strong>{{ $purchaseOrder->revision ?? '-' }}</strong></div>
        </div>

        <div class="addr-grid">
            <div class="addr-box">
                <label>To</label>
                <p>{{ $purchaseOrder->to_address ?? '-' }}</p>
                <label>Attn</label>
                <p>{{ $purchaseOrder->attn ?? '-' }}</p>
            </div>
            <div class="addr-box">
                <label>Invoice Address</label>
                <p>{{ $purchaseOrder->invoice_address ?? '-' }}</p>
            </div>
        </div>

        <div class="addr-grid">
            <div class="addr-box">
                <label>Subject</label>
                <p>{{ $purchaseOrder->subject ?? '-' }}</p>
                <label>Project Description</label>
                <p>{{ $purchaseOrder->project_description ?? '-' }}</p>
                <label>Contact Number</label>
                <p>{{ $purchaseOrder->contact_number ?? '-' }}</p>
                <label>Client</label>
                <p>{{ $purchaseOrder->client ?? '-' }}</p>
            </div>
            <div class="addr-box">
                <label>Final Delivery Address</label>
                <p>{{ $purchaseOrder->final_delivery_address ?? '-' }}</p>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th class="num">No</th>
                    <th>Description</th>
                    <th class="qty">Qty</th>
                    <th class="uom">UOM</th>
                    <th class="brand">Brand</th>
                    <th class="price">Unit Price</th>
                    <th class="total">Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td class="num">{{ $item->line_no }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="qty">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}</td>
                        <td class="uom">{{ $item->uom }}</td>
                        <td class="brand">{{ $item->brand ?? '-' }}</td>
                        <td class="price">{{ number_format((float) $item->price, 0, ',', '.') }}</td>
                        <td class="total">{{ number_format((float) $item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <table>
                <tr>
                    <td>Total</td>
                    <td>Rp {{ number_format((float) $purchaseOrder->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($purchaseOrder->use_ppn)
                    <tr>
                        <td>PPN {{ rtrim(rtrim(number_format((float) $purchaseOrder->ppn_percent, 2, '.', ''), '0'), '.') }}%</td>
                        <td>Rp {{ number_format((float) $purchaseOrder->ppn_amount, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td>Grand Total</td>
                    <td>Rp {{ number_format((float) $purchaseOrder->grand_total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="signatures">
            @foreach($purchaseOrder->approvals as $approval)
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

        <div class="vendor-acceptance">
            <h3>Vendor Acceptance</h3>
            <div class="grid">
                <div class="box">
                    <div class="sig-space"></div>
                    <label>Signature &amp; Company Stamp</label>
                </div>
                <div class="box">
                    <div class="sig-space"></div>
                    <label>Name &amp; Position</label>
                </div>
                <div class="box">
                    <div class="sig-space"></div>
                    <label>Date</label>
                </div>
            </div>
        </div>

        <p class="footer-note">Generated from {{ config('app.name', 'e-Procure') }} Purchase Order Management on {{ now()->format('d-m-Y H:i') }}.</p>
    </div>
</body>
</html>
