<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f7f7f7;
            padding: 40px;
            color: #333;
        }

        .receipt-wrapper {
            max-width: 700px;
            margin: auto;
        }

        .receipt-box {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border: 1px solid #eee;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            letter-spacing: 3px;
            font-size: 26px;
        }

        .sub {
            font-size: 12px;
            color: #777;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 14px;
        }

        .label {
            font-weight: 600;
            color: #555;
        }

        .value {
            text-align: right;
        }

        .total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px dashed #ddd;
            font-size: 16px;
            font-weight: bold;
        }

        .highlight {
            color: #1a73e8;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #888;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .receipt-box {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

<div class="receipt-wrapper">
    <div class="receipt-box">

        <div class="header">
            <h1>RECEIPT</h1>
            <div class="sub">Payment Acknowledgement Slip</div>
        </div>

        <div class="row">
            <div class="label">Receipt No</div>
            <div class="value">{{ $receipt->receipt_number }}</div>
        </div>

        <div class="row">
            <div class="label">Tenant</div>
            <div class="value">{{ $receipt->deposit->tenancy->tenant->full_name }}</div>
        </div>

        <div class="row">
            <div class="label">Unit</div>
            <div class="value">{{ $receipt->deposit->tenancy->unit->unit_number }}</div>
        </div>

        <div class="row">
            <div class="label">Payment Method</div>
            <div class="value">{{ $receipt->payment_method }}</div>
        </div>

        @if($receipt->mpesa_code)
        <div class="row">
            <div class="label">M-Pesa Code</div>
            <div class="value">{{ $receipt->mpesa_code }}</div>
        </div>
        @endif

        <div class="row total">
            <div class="label">Amount Paid</div>
            <div class="value highlight">KES {{ number_format($receipt->amount, 2) }}</div>
        </div>

        <div class="row">
            <div class="label">Balance</div>
            <div class="value">KES {{ number_format($receipt->data['balance'] ?? 0, 2) }}</div>
        </div>

        <div class="row">
            <div class="label">Date</div>
            <div class="value">
                {{ \Carbon\Carbon::parse($receipt->issued_at)->format('d/m/Y') }}
            </div>
        </div>

        <div class="footer">
            Thank you for your payment.
        </div>

    </div>
</div>

</body>
</html>