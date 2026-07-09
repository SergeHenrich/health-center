<table>
    <thead>
        <tr>
            <th>Mois</th>
            <th>Dispensations</th>
        </tr>
    </thead>
    <tbody>
        @foreach($chartData['labels'] ?? $chartData as $index => $label)
        <tr>
            <td>{{ $label }}</td>
            <td>{{ $chartData['dispensed'][$index] ?? $chartData['data'][$index] ?? 0 }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
