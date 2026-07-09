<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyDocument;
use App\Services\Pharmacy\PharmacyDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PharmacyDocumentController extends Controller
{
    public function __construct(private readonly PharmacyDocumentService $documentService) {}

    public function index(Request $request): View
    {
        $documents = $this->documentService->getDocuments($request->only(['type', 'q', 'active']));
        return view('pharmacy.documents.index', compact('documents'));
    }

    public function create(): View
    {
        return view('pharmacy.documents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'type' => 'required|in:sop,contract,regulatory,reference,other',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg|max:20480',
            'version' => 'nullable|string|max:20',
            'medicine_id' => 'nullable|exists:medicines,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expiry_date' => 'nullable|date',
        ]);

        $this->documentService->uploadDocument($validated);

        return redirect()->route('pharmacy-documents.index')
            ->with('success', 'Document uploadé avec succès.');
    }

    public function show(PharmacyDocument $pharmacyDocument): View
    {
        $pharmacyDocument->load(['uploader', 'medicine', 'supplier']);
        return view('pharmacy.documents.show', compact('pharmacyDocument'));
    }

    public function edit(PharmacyDocument $pharmacyDocument): View
    {
        return view('pharmacy.documents.edit', compact('pharmacyDocument'));
    }

    public function update(Request $request, PharmacyDocument $pharmacyDocument): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'type' => 'required|in:sop,contract,regulatory,reference,other',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg|max:20480',
            'version' => 'nullable|string|max:20',
            'medicine_id' => 'nullable|exists:medicines,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'expiry_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $this->documentService->updateDocument($pharmacyDocument, $validated);

        return redirect()->route('pharmacy-documents.show', $pharmacyDocument)
            ->with('success', 'Document mis à jour.');
    }

    public function destroy(PharmacyDocument $pharmacyDocument): RedirectResponse
    {
        $this->documentService->deleteDocument($pharmacyDocument);

        return redirect()->route('pharmacy-documents.index')
            ->with('success', 'Document supprimé.');
    }
}
