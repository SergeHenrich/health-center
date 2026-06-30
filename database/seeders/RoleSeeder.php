<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Patients
            'patients.view', 'patients.create', 'patients.edit', 'patients.delete',
            // Appointments
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
            // Medical
            'consultations.view', 'consultations.create', 'consultations.edit', 'consultations.close',
            'prescriptions.view', 'prescriptions.create',
            // Lab
            'lab.requests.view', 'lab.requests.create', 'lab.results.create', 'lab.results.validate',
            // Pharmacy
            'pharmacy.dispense', 'pharmacy.stock.view', 'pharmacy.stock.edit', 'pharmacy.orders.create',
            'pharmacy.pos.sell', 'pharmacy.pos.annul', 'pharmacy.pos.discount',
            'pharmacy.price.edit', 'pharmacy.stock.receive', 'pharmacy.stock.inventory',
            'pharmacy.validation', 'pharmacy.narcotics', 'pharmacy.orders.approve',
            'pharmacy.suppliers.manage', 'pharmacy.reports.financial', 'pharmacy.reports.daily',
            'pharmacy.config.settings', 'pharmacy.patient.view', 'pharmacy.patient.edit',
            // Hospitalization
            'hospitalization.view', 'hospitalization.admit', 'hospitalization.discharge',
            // Billing
            'billing.invoices.view', 'billing.invoices.create', 'billing.payments.create',
            'billing.reports.view', 'billing.expenses.create',
            // Admin
            'admin.settings.edit', 'admin.audit.view', 'admin.roles.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'administrator' => $permissions, // all permissions

            'director' => [
                'patients.view', 'appointments.view', 'consultations.view',
                'billing.invoices.view', 'billing.reports.view',
                'hospitalization.view', 'pharmacy.stock.view', 'users.view',
            ],

            'general_practitioner' => [
                'patients.view', 'patients.edit',
                'appointments.view', 'appointments.create', 'appointments.edit',
                'consultations.view', 'consultations.create', 'consultations.edit', 'consultations.close',
                'prescriptions.view', 'prescriptions.create',
                'lab.requests.view', 'lab.requests.create', 'lab.results.validate',
                'hospitalization.view', 'hospitalization.admit', 'hospitalization.discharge',
            ],

            'specialist' => [
                'patients.view',
                'consultations.view', 'consultations.create', 'consultations.edit', 'consultations.close',
                'prescriptions.view', 'prescriptions.create',
                'lab.requests.view', 'lab.requests.create', 'lab.results.validate',
                'hospitalization.view',
            ],

            'nurse' => [
                'patients.view',
                'consultations.view',
                'lab.results.create',
                'hospitalization.view',
            ],

            'pharmacist' => [
                'prescriptions.view',
                'pharmacy.dispense', 'pharmacy.stock.view', 'pharmacy.stock.edit', 'pharmacy.orders.create',
                'pharmacy.pos.sell', 'pharmacy.pos.annul', 'pharmacy.pos.discount',
                'pharmacy.stock.receive', 'pharmacy.stock.inventory',
                'pharmacy.validation', 'pharmacy.narcotics', 'pharmacy.orders.approve',
                'pharmacy.reports.daily', 'pharmacy.patient.view', 'pharmacy.patient.edit',
            ],

            'preparateur' => [
                'pharmacy.pos.sell',
                'pharmacy.stock.view', 'pharmacy.stock.receive', 'pharmacy.stock.inventory',
                'pharmacy.patient.view',
                'patients.view', 'patients.create', 'patients.edit',
            ],

            'stock_manager' => [
                'pharmacy.stock.view', 'pharmacy.stock.edit', 'pharmacy.stock.receive', 'pharmacy.stock.inventory',
                'pharmacy.orders.create',
            ],

            'cashier' => [
                'patients.view',
                'billing.invoices.view', 'billing.invoices.create',
                'billing.payments.create', 'billing.reports.view', 'billing.expenses.create',
            ],

            'receptionist' => [
                'patients.view', 'patients.create', 'patients.edit',
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel',
            ],

            'patient' => [
                'appointments.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
