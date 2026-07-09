<?php

namespace Database\Seeders;

use App\Models\LabExam;
use Illuminate\Database\Seeder;

class LabExamSeeder extends Seeder
{
    public function run(): void
    {
        $exams = [
            ['code' => 'LAB-HEM', 'name' => 'Numération formule sanguine (NFS)', 'category' => 'Hématologie', 'unit' => '', 'normal_range' => 'Variable selon paramètre', 'price' => 3000, 'turnaround_hours' => 4],
            ['code' => 'LAB-GLY', 'name' => 'Glycémie à jeun', 'category' => 'Biochimie', 'unit' => 'mg/dL', 'normal_range' => '70-100', 'price' => 1500, 'turnaround_hours' => 2],
            ['code' => 'LAB-CREA', 'name' => 'Créatinine', 'category' => 'Biochimie', 'unit' => 'mg/dL', 'normal_range' => '0.6-1.2', 'price' => 2000, 'turnaround_hours' => 4],
            ['code' => 'LAB-URE', 'name' => 'Urée', 'category' => 'Biochimie', 'unit' => 'mg/dL', 'normal_range' => '10-50', 'price' => 1800, 'turnaround_hours' => 4],
            ['code' => 'LAB-PAL', 'name' => 'Test de paludisme (Goutte épaisse)', 'category' => 'Parasitologie', 'unit' => '', 'normal_range' => 'Négatif', 'price' => 2500, 'turnaround_hours' => 1],
            ['code' => 'LAB-TYPH', 'name' => 'Test typhoïde (Widal)', 'category' => 'Sérologie', 'unit' => '', 'normal_range' => 'Négatif', 'price' => 3500, 'turnaround_hours' => 6],
            ['code' => 'LAB-HIV', 'name' => 'Sérologie VIH', 'category' => 'Sérologie', 'unit' => '', 'normal_range' => 'Négatif', 'price' => 4000, 'turnaround_hours' => 2],
            ['code' => 'LAB-HEP', 'name' => 'Sérologie Hépatite B', 'category' => 'Sérologie', 'unit' => '', 'normal_range' => 'Négatif', 'price' => 4500, 'turnaround_hours' => 6],
            ['code' => 'LAB-URIN', 'name' => 'Examen cytobactériologique des urines (ECBU)', 'category' => 'Bactériologie', 'unit' => '', 'normal_range' => 'Stérile', 'price' => 3000, 'turnaround_hours' => 24],
            ['code' => 'LAB-CHOL', 'name' => 'Cholestérol total', 'category' => 'Biochimie', 'unit' => 'mg/dL', 'normal_range' => '<200', 'price' => 2000, 'turnaround_hours' => 4],
            ['code' => 'LAB-TRIG', 'name' => 'Triglycérides', 'category' => 'Biochimie', 'unit' => 'mg/dL', 'normal_range' => '<150', 'price' => 2000, 'turnaround_hours' => 4],
            ['code' => 'LAB-TSH', 'name' => 'TSH (thyroïde)', 'category' => 'Endocrinologie', 'unit' => 'mUI/L', 'normal_range' => '0.4-4.0', 'price' => 5000, 'turnaround_hours' => 24],
            ['code' => 'LAB-GROUPE', 'name' => 'Groupage sanguin ABO/Rhésus', 'category' => 'Hématologie', 'unit' => '', 'normal_range' => 'N/A', 'price' => 2500, 'turnaround_hours' => 2],
            ['code' => 'LAB-CRP', 'name' => 'Protéine C réactive (CRP)', 'category' => 'Immunologie', 'unit' => 'mg/L', 'normal_range' => '<5', 'price' => 3500, 'turnaround_hours' => 4],
            ['code' => 'LAB-SELLE', 'name' => 'Examen parasitologique des selles', 'category' => 'Parasitologie', 'unit' => '', 'normal_range' => 'Absence de parasites', 'price' => 2000, 'turnaround_hours' => 24],
        ];

        foreach ($exams as $exam) {
            $exam['is_active'] = true;
            LabExam::firstOrCreate(['code' => $exam['code']], $exam);
        }
    }
}
