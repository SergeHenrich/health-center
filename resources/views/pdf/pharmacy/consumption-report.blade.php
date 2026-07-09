<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de consommation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; margin-bottom: 4px; }
        .meta { color: #666; font-size: 9pt; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563eb; color: white; padding: 6px 8px; text-align: left; font-size: 9pt; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9pt; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Rapport de consommation</h1>
    <p class="meta">Généré le {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Période</th>
                <th class="text-right">Dispensations</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chartData['labels'] ?? [] as $i => $label)
            <tr>
                <td>{{ $label }}</td>
                <td class="text-right">{{ $chartData['dispensed'][$i] ?? 0 }}</td>
            </tr>
            @empty
            <tr><td colspan="2" style="text-align:center;color:#999;padding:20px;">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
