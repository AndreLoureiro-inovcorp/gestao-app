<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovativo de Pagamento</title>
</head>
<body>
    <div class="header">
        <h2>Comprovativo de Pagamento</h2>
    </div>

    <div class="content">
        <p>Estimado(a) <strong>{{ $invoice->supplier->name }}</strong>,</p>

        <p>Enviamos em anexo o comprovativo de pagamento da fatura abaixo:</p>

        <div class="invoice-details">
            <p><strong>Número da Fatura:</strong> {{ $invoice->number }}</p>
            <p><strong>Valor Total:</strong> {{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
            <p><strong>Data de Pagamento:</strong> {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y') : '-' }}</p>
        </div>

        <p>Qualquer questão, entre em contacto connosco.</p>

        <p>Cumprimentos,<br>
            <strong>{{ config('app.name') }}</strong></p>
    </div>

    <div class="footer">
        <p>Este é um email automático. Por favor, não responda a esta mensagem.</p>
    </div>
</body>
</html>
