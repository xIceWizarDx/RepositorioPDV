<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Orçamento #{{ $orcamento->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f5f5f5; }
    </style>
</head>
<body onload="window.print()">
    <h2>Orçamento #{{ $orcamento->id }}</h2>
    <p>Cliente: {{ $orcamento->cliente->nome_razao ?? '' }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produto</th>
                <th>Qtd</th>
                <th>Preço</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itens as $idx => $item)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $item->descricao }}</td>
                <td>{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                <td>{{ number_format($item->preco, 2, ',', '.') }}</td>
                <td>{{ number_format($item->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

