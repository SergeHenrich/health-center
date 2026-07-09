<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormularyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'Formulary',
            'email'      => 'admin@formulary.test',
            'username'   => 'admin_formulary',
            'is_active'  => true,
        ]);
        $this->admin->assignRole('administrator');
    }

    public function test_formulary_list_can_be_viewed(): void
    {
        Medicine::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('formulary.index'))
            ->assertOk();
    }

    public function test_formulary_show_displays_medicine_details(): void
    {
        $medicine = Medicine::factory()->create([
            'formulary_status' => 'inscrit',
            'atc_code' => 'N02BE01',
            'therapeutic_class' => 'Analgésique',
        ]);

        $this->actingAs($this->admin)
            ->get(route('formulary.show', $medicine))
            ->assertOk()
            ->assertSee($medicine->name)
            ->assertSee('N02BE01')
            ->assertSee('Analgésique');
    }

    public function test_formulary_status_can_be_updated(): void
    {
        $medicine = Medicine::factory()->create(['formulary_status' => 'inscrit']);

        $this->actingAs($this->admin)
            ->put(route('formulary.update-status', $medicine), [
                'formulary_status' => 'non_inscrit',
                'atc_code' => 'A01AA01',
                'therapeutic_class' => 'Antibiotique',
            ])
            ->assertSessionHas('success');

        $medicine->refresh();
        $this->assertEquals('non_inscrit', $medicine->formulary_status);
        $this->assertEquals('A01AA01', $medicine->atc_code);
        $this->assertEquals('Antibiotique', $medicine->therapeutic_class);
    }

    public function test_commission_decision_can_be_recorded(): void
    {
        $medicine = Medicine::factory()->create(['formulary_status' => 'non_inscrit']);

        $this->actingAs($this->admin)
            ->post(route('formulary.commission-decision.store', $medicine), [
                'decision' => 'admission',
                'justification' => 'Ajout au livret pour prise en charge du paludisme.',
                'decision_date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHas('success');

        $this->assertEquals('inscrit', $medicine->fresh()->formulary_status);
        $this->assertCount(1, $medicine->fresh()->commissionDecisions);
    }

    public function test_therapeutic_substitution_can_be_registered(): void
    {
        $medicine = Medicine::factory()->create();
        $substitute = Medicine::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('formulary.substitutions.store', $medicine), [
                'substitute_medicine_id' => $substitute->id,
                'substitution_type' => 'generic',
                'reason' => 'Alternative thérapeutique',
            ])
            ->assertSessionHas('success');

        $this->assertCount(1, $medicine->fresh()->therapeuticSubstitutions);
    }

    public function test_medicine_formulary_scopes(): void
    {
        Medicine::factory()->create(['formulary_status' => 'inscrit']);
        Medicine::factory()->create(['formulary_status' => 'non_inscrit']);
        Medicine::factory()->create(['formulary_status' => 'non_inscrit', 'is_narcotic' => true]);
        Medicine::factory()->create(['formulary_status' => 'non_inscrit', 'is_cold_chain' => true]);

        $this->assertEquals(1, Medicine::onFormulary()->count());
        $this->assertEquals(1, Medicine::narcotic()->count());
        $this->assertEquals(1, Medicine::coldChain()->count());
    }
}
