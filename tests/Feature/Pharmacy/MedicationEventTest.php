<?php

namespace Tests\Feature\Pharmacy;

use App\Models\MedicationEvent;
use App\Models\Medicine;
use App\Models\User;
use App\Services\Pharmacy\MedicationEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationEventTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;
    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Event',
            'last_name'  => 'Test',
            'email'      => 'events@test.test',
            'username'   => 'events_test',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

        $this->medicine = Medicine::factory()->create();
    }

    public function test_event_list_can_be_viewed(): void
    {
        MedicationEvent::factory()->count(3)->create();

        $this->actingAs($this->pharmacist)
            ->get(route('medication-events.index'))
            ->assertOk();
    }

    public function test_event_creation_form_can_be_viewed(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('medication-events.create'))
            ->assertOk()
            ->assertSee('Signaler un événement');
    }

    public function test_event_can_be_reported(): void
    {
        $this->actingAs($this->pharmacist);

        $service = app(MedicationEventService::class);
        $event = $service->reportEvent([
            'type' => 'medication_error',
            'severity' => 'high',
            'medicine_id' => $this->medicine->id,
            'description' => 'Erreur de dosage sur la dispensation.',
            'cause' => 'Confusion entre deux conditionnements.',
            'action_taken' => 'Information du pharmacien responsable.',
        ]);

        $this->assertNotNull($event);
        $this->assertEquals('medication_error', $event->type);
        $this->assertEquals('high', $event->severity);
        $this->assertEquals('open', $event->status);
    }

    public function test_event_can_be_resolved(): void
    {
        $this->actingAs($this->pharmacist);

        $service = app(MedicationEventService::class);
        $event = $service->reportEvent([
            'type' => 'adverse_drug_reaction',
            'severity' => 'critical',
            'description' => 'Réaction allergique sévère.',
        ]);

        $resolved = $service->resolveEvent($event, 'Patient pris en charge aux urgences.', 'Révision du protocole d\'administration.');

        $this->assertEquals('resolved', $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
        $this->assertStringContainsString('urgences', $resolved->action_taken);
    }

    public function test_event_can_be_reported_via_http(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('medication-events.store'), [
                'type' => 'near_miss',
                'severity' => 'medium',
                'description' => 'Presque-accident détecté lors de la validation.',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(1, MedicationEvent::count());
    }

    public function test_event_detail_can_be_viewed(): void
    {
        $event = MedicationEvent::factory()->create(['reported_by' => $this->pharmacist->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('medication-events.show', $event))
            ->assertOk()
            ->assertSee($event->description);
    }

    public function test_event_can_be_resolved_via_http(): void
    {
        $event = MedicationEvent::factory()->create(['reported_by' => $this->pharmacist->id]);

        $this->actingAs($this->pharmacist)
            ->post(route('medication-events.resolve', $event), [
                'action_taken' => 'Action corrective mise en place.',
                'corrective_actions' => 'Révision des procédures.',
            ])
            ->assertSessionHas('success');

        $this->assertEquals('resolved', $event->fresh()->status);
    }
}
