<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Encomenda {{ $order->number }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .container {
            width: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-name {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .document-title {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }

        .document-number {
            text-align: right;
            font-size: 11px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .client-section {
            margin-bottom: 15px;
        }

        .metadata {
            margin-bottom: 15px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border-bottom: 1px solid #ccc;
            padding: 6px 4px;
        }

        th {
            font-size: 10px;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 40%;
            margin-left: auto;
            margin-top: 15px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }

        .total-final {
            font-weight: bold;
            border-top: 1px solid #000;
            margin-top: 5px;
            padding-top: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 9px;
            text-align: center;
        }

    </style>
</head>

<body>
    <div class="container">

        <div class="header">
            <div>
                <div class="company-name">{{ config('app.name') }}</div>
                @if($companySetting)
                {{ $companySetting->name }}<br>
                {{ $companySetting->address }}<br>
                {{ $companySetting->postal_code }} {{ $companySetting->city }}<br>
                NIF: {{ $companySetting->tax_number }}
                @endif
            </div>

            <div>
                <div class="document-title">ENCOMENDA</div>
                <div class="document-number">{{ $order->number }}</div>
            </div>
        </div>

        <div class="client-section">
            <div class="section-title">Cliente</div>
            {{ $order->client->name }}<br>
            {{ $order->client->address }}<br>
            {{ $order->client->postal_code }} {{ $order->client->city }}<br>
            NIF: {{ $order->client->tax_number }}
        </div>

        <div class="metadata">
            Data: {{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}
            @if($order->proposal)
            | Proposta: {{ $order->proposal->number }}
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Ref.</th>
                    <th>Artigo</th>
                    <th class="text-center">Qtd</th>
                    <th class="text-right">Preço</th>
                    <th class="text-center">IVA</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>

            <tbody>
                @php
                $subtotalWithoutVat = 0;
                $totalVat = 0;
                @endphp

                @foreach($order->clientOrderArticles as $line)
                @php
                $lineSubtotal = $line->quantity * $line->unit_price;
                $vatRate = $line->article->vatRate->rate ?? 0;
                $lineVat = $lineSubtotal * ($vatRate / 100);
                $lineTotal = $lineSubtotal + $lineVat;

                $subtotalWithoutVat += $lineSubtotal;
                $totalVat += $lineVat;
                @endphp

                <tr>
                    <td>{{ $line->article->reference }}</td>
                    <td>{{ $line->article->name }}</td>
                    <td class="text-center">{{ number_format($line->quantity, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($line->unit_price, 2, ',', '.') }} €</td>
                    <td class="text-center">{{ $vatRate }}%</td>
                    <td class="text-right">{{ number_format($lineTotal, 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span>Subtotal</span>
                <span>{{ number_format($subtotalWithoutVat, 2, ',', '.') }} €</span>
            </div>
            <div class="totals-row">
                <span>IVA</span>
                <span>{{ number_format($totalVat, 2, ',', '.') }} €</span>
            </div>
            <div class="totals-row total-final">
                <span>TOTAL</span>
                <span>{{ number_format($order->total_amount, 2, ',', '.') }} €</span>
            </div>
        </div>

        @if($order->notes)
        <div class="client-section">
            <div class="section-title">Observações</div>
            {{ $order->notes }}
        </div>
        @endif

        <div class="footer">
            Documento gerado automaticamente em {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>
</body>
</html>
