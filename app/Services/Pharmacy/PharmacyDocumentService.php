<?php

namespace App\Services\Pharmacy;

use App\Models\PharmacyDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PharmacyDocumentService
{
    public function getDocuments(array $filters = [])
    {
        return PharmacyDocument::with(['uploader', 'medicine', 'supplier'])
            ->when($filters['type'] ?? null, fn($q, $v) => $q->where('type', $v))
            ->when($filters['q'] ?? null, fn($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->when($filters['active'] ?? null, fn($q) => $q->where('is_active', true))
            ->latest()
            ->paginate(25);
    }

    public function uploadDocument(array $data): PharmacyDocument
    {
        return DB::transaction(function () use ($data) {
            $file = $data['file'];
            $path = $file->store('pharmacy-documents', 'public');

            return PharmacyDocument::create([
                'title' => $data['title'],
                'type' => $data['type'],
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'version' => $data['version'] ?? '1.0',
                'uploaded_by' => $data['uploaded_by'] ?? auth()->id(),
                'medicine_id' => $data['medicine_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'is_active' => true,
                'published_at' => now(),
            ]);
        });
    }

    public function updateDocument(PharmacyDocument $document, array $data): PharmacyDocument
    {
        return DB::transaction(function () use ($document, $data) {
            if (isset($data['file'])) {
                Storage::disk('public')->delete($document->file_path);
                $file = $data['file'];
                $path = $file->store('pharmacy-documents', 'public');

                $data['file_path'] = $path;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
            }

            $document->update($data);

            return $document;
        });
    }

    public function deleteDocument(PharmacyDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }
}
