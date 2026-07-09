<table>
    <thead>
        <tr>
            <th>Médicament</th>
            <th>N° lot</th>
            <th>Péremption</th>
            <th>Disponible</th>
            <th>Initiale</th>
            <th>Statut</th>
            <th>Prix unit.</th>
        </tr>
    </thead>
    <tbody>
        @foreach($batches as $batch)
        <tr>
            <td>{{ $batch->medicine->name }}</td>
            <td>{{ $batch->lot_number ?? '-' }}</td>
            <td>{{ $batch->expiry_date?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $batch->quantity_available }}</td>
            <td>{{ $batch->initial_quantity }}</td>
            <td>{{ $batch->status }}</td>
            <td>{{ $batch->unit_cost ? number_format($batch->unit_cost, 2) . ' €' : '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
