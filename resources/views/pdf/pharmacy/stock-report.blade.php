<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport des stocks</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        h1 { font-size: 16pt; margin-bottom: 4px; }
        .meta { color: #666; font-size: 9pt; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563eb; color: white; padding: 6px 8px; text-align: left; font-size: 9pt; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9pt; }
        .summary { display: flex; gap: 20px; margin-bottom: 16px; }
        .summary div { background: #f3f4f6; padding: 8px 14px; border-radius: 6px; }
        .summary strong { display: block; font-size: 14pt; }
        .summary small { color: #666; font-size: 8pt; }
        .text-right { text-align: right; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>
    <h1>Rapport des stocks</h1>
    <p class="meta">Généré le {{ now()->format('d/m/Y H:i') }}</p>

    <div class="summary">
        <div><small>Total lots</small><strong>{{ $totals['batches'] }}</strong></div>
        <div><small>Qté disponible</small><strong>{{ $totals['total_available'] }}</strong></div>
        <div><small>Qté initiale</small><strong>{{ $totals['total_initial'] }}</strong></div>
        <div><small>Lots périmés</small><strong class="text-red">{{ $totals['expired_count'] }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Médicament</th>
                <th>N° lot</th>
                <th>Péremption</th>
                <th class="text-right">Disponible</th>
                <th class="text-right">Initiale</th>
                <th>Statut</th>
                <th class="text-right">Prix unit.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $batch)
            <tr>
                <td>{{ $batch->medicine->name }}</td>
                <td>{{ $batch->lot_number ?? '-' }}</td>
                <td>{{ $batch->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                <td class="text-right">{{ $batch->quantity_available }}</td>
                <td class="text-right">{{ $batch->initial_quantity }}</td>
                <td>{{ $batch->status }}</td>
                <td class="text-right">{{ $batch->unit_cost ? number_format($batch->unit_cost, 2) . ' €' : '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#999;padding:20px;">Aucun lot.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
