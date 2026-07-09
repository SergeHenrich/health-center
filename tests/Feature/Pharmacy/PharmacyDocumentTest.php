<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\PharmacyDocument;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Pharmacy\PharmacyDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PharmacyDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Doc',
            'last_name'  => 'Test',
            'email'      => 'doc@test.test',
            'username'   => 'doc_test',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');
    }

    public function test_document_list_can_be_viewed(): void
    {
        PharmacyDocument::factory()->count(3)->create();

        $this->actingAs($this->pharmacist)
            ->get(route('pharmacy-documents.index'))
            ->assertOk();
    }

    public function test_document_creation_form_can_be_viewed(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('pharmacy-documents.create'))
            ->assertOk()
            ->assertSee('Ajouter un document');
    }

    public function test_document_can_be_uploaded(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('procedure.pdf', 200, 'application/pdf');

        $this->actingAs($this->pharmacist)
            ->post(route('pharmacy-documents.store'), [
                'title' => 'Procédure de validation',
                'type' => 'sop',
                'reference' => 'SOP-PH-001',
                'description' => 'Description de la procédure.',
                'file' => $file,
                'version' => '1.0',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(1, PharmacyDocument::count());
        Storage::disk('public')->assertExists(PharmacyDocument::first()->file_path);
    }

    public function test_document_can_be_viewed(): void
    {
        $document = PharmacyDocument::factory()->create(['uploaded_by' => $this->pharmacist->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('pharmacy-documents.show', $document))
            ->assertOk()
            ->assertSee($document->title);
    }

    public function test_document_can_be_updated(): void
    {
        $document = PharmacyDocument::factory()->create(['uploaded_by' => $this->pharmacist->id]);

        $this->actingAs($this->pharmacist)
            ->put(route('pharmacy-documents.update', $document), [
                'title' => 'Titre modifié',
                'type' => 'contract',
                'is_active' => true,
            ])
            ->assertSessionHas('success');

        $this->assertEquals('Titre modifié', $document->fresh()->title);
        $this->assertEquals('contract', $document->fresh()->type);
    }

    public function test_document_can_be_deleted(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $path = $file->store('pharmacy-documents', 'public');

        $document = PharmacyDocument::factory()->create([
            'uploaded_by' => $this->pharmacist->id,
            'file_path' => $path,
        ]);

        $this->actingAs($this->pharmacist)
            ->delete(route('pharmacy-documents.destroy', $document))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($document);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_document_can_be_linked_to_medicine(): void
    {
        $medicine = Medicine::factory()->create();

        $document = PharmacyDocument::factory()->create([
            'uploaded_by' => $this->pharmacist->id,
            'medicine_id' => $medicine->id,
        ]);

        $this->assertNotNull($document->medicine);
        $this->assertEquals($medicine->id, $document->medicine->id);
    }

    public function test_document_can_be_linked_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $document = PharmacyDocument::factory()->create([
            'uploaded_by' => $this->pharmacist->id,
            'supplier_id' => $supplier->id,
        ]);

        $this->assertNotNull($document->supplier);
        $this->assertEquals($supplier->id, $document->supplier->id);
    }

    public function test_document_edit_form_can_be_viewed(): void
    {
        $document = PharmacyDocument::factory()->create(['uploaded_by' => $this->pharmacist->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('pharmacy-documents.edit', $document))
            ->assertOk()
            ->assertSee($document->title);
    }
}
