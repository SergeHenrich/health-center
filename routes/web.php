<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Patient\QueueController;
use App\Http\Controllers\Medical\ConsultationController;
use App\Http\Controllers\Medical\DiagnosisController;
use App\Http\Controllers\Medical\PrescriptionController;
use App\Http\Controllers\Medical\VitalSignController;
use App\Http\Controllers\Laboratory\LabRequestController;
use App\Http\Controllers\Laboratory\LabResultController;
use App\Http\Controllers\Pharmacy\MedicineController;
use App\Http\Controllers\Pharmacy\StockController;
use App\Http\Controllers\Pharmacy\DispensationController;
use App\Http\Controllers\Pharmacy\PurchaseOrderController;
use App\Http\Controllers\Pharmacy\SupplierController;
use App\Http\Controllers\Pharmacy\WarehouseController;
use App\Http\Controllers\Pharmacy\FormularyController;
use App\Http\Controllers\Pharmacy\PharmaceuticalValidationController;
use App\Http\Controllers\Pharmacy\PosController;
use App\Http\Controllers\Pharmacy\NarcoticRegisterController;
use App\Http\Controllers\Hospitalization\RoomController;
use App\Http\Controllers\Hospitalization\HospitalizationController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\Billing\ExpenseController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

// ── Auth (public) ─────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Authenticated routes ──────────────────────────────────────
Route::middleware(['auth', 'App\Http\Middleware\AuditLogger'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ── Patients ──────────────────────────────────────────────
    Route::middleware('role:administrator|receptionist|general_practitioner|specialist|nurse')
        ->group(function () {
            Route::resource('patients', PatientController::class);
            Route::resource('appointments', AppointmentController::class)->except(['destroy']);
            Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
            Route::post('/queue/{queue}/call', [QueueController::class, 'call'])->name('queue.call');
            Route::post('/queue/{queue}/complete', [QueueController::class, 'complete'])->name('queue.complete');
        });

    // ── Medical ───────────────────────────────────────────────
    Route::middleware('role:general_practitioner|specialist|administrator')
        ->group(function () {
            Route::resource('consultations', ConsultationController::class)->only(['index', 'create', 'store', 'show', 'update']);
            Route::post('consultations/{consultation}/close', [ConsultationController::class, 'close'])
                ->name('consultations.close');
            Route::resource('consultations.diagnoses', DiagnosisController::class)->shallow()->only(['store', 'destroy']);
            Route::resource('consultations.prescriptions', PrescriptionController::class)->shallow()->only(['create', 'store', 'show']);
        });

    // ── Vital Signs (Nurse + Doctor) ──────────────────────────
    Route::middleware('role:nurse|general_practitioner|specialist|administrator')
        ->group(function () {
            Route::resource('vital-signs', VitalSignController::class)->only(['store', 'update']);
        });

    // ── Laboratory ────────────────────────────────────────────
    Route::middleware('role:general_practitioner|specialist|administrator')
        ->group(function () {
            Route::resource('lab-requests', LabRequestController::class)->only(['index', 'create', 'store', 'show']);
        });

    Route::middleware('role:general_practitioner|specialist|administrator|nurse')
        ->group(function () {
            Route::resource('lab-results', LabResultController::class)->only(['index', 'store']);
            Route::post('lab-results/{result}/validate', [LabResultController::class, 'validate'])
                ->name('lab-results.validate');
        });

    // ── Pharmacy ──────────────────────────────────────────────
    Route::middleware('role:pharmacist|preparateur|stock_manager|administrator')
        ->group(function () {
            Route::resource('medicines', MedicineController::class)->except(['edit']);
            Route::get('stock', [StockController::class, 'index'])->name('stock.index');
            Route::post('stock/{stock}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
            Route::resource('dispensations', DispensationController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
            Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
            Route::post('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])
                ->name('purchase-orders.approve');
            Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
                ->name('purchase-orders.receive');

            // Phase 1 — Suppliers
            Route::resource('suppliers', SupplierController::class);
            Route::post('suppliers/{supplier}/contracts', [SupplierController::class, 'storeContract'])->name('suppliers.contracts.store');
            Route::post('suppliers/{supplier}/evaluations', [SupplierController::class, 'storeEvaluation'])->name('suppliers.evaluations.store');

            // Phase 1 — Warehouses
            Route::resource('warehouses', WarehouseController::class)->except(['destroy']);
            Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
            Route::post('warehouses/stock/{warehouseStock}/adjust', [WarehouseController::class, 'adjustStock'])->name('warehouses.stock.adjust');
            Route::post('warehouses/transfer', [WarehouseController::class, 'transfer'])->name('warehouses.transfer');

            // Phase 1 — Formulary
            Route::get('formulary', [FormularyController::class, 'index'])->name('formulary.index');
            Route::get('formulary/{medicine}', [FormularyController::class, 'show'])->name('formulary.show');
            Route::put('formulary/{medicine}/status', [FormularyController::class, 'updateStatus'])->name('formulary.update-status');
            Route::post('formulary/{medicine}/commission-decision', [FormularyController::class, 'storeCommissionDecision'])->name('formulary.commission-decision.store');
            Route::post('formulary/{medicine}/substitutions', [FormularyController::class, 'storeSubstitution'])->name('formulary.substitutions.store');
            Route::delete('formulary/substitutions/{substitution}', [FormularyController::class, 'destroySubstitution'])->name('formulary.substitutions.destroy');

            // Phase 1 — Clinical Pharmacy (Validation)
            Route::get('validations', [PharmaceuticalValidationController::class, 'index'])->name('validations.index');
            Route::get('validations/{prescription}', [PharmaceuticalValidationController::class, 'show'])->name('validations.show');
            Route::post('validations/{prescription}/validate', [PharmaceuticalValidationController::class, 'validatePrescription'])->name('validations.validate');

            // Phase 1 — Narcotics
            Route::get('narcotics', [NarcoticRegisterController::class, 'index'])->name('narcotics.index');
            Route::get('narcotics/{medicine}', [NarcoticRegisterController::class, 'show'])->name('narcotics.show');
            Route::post('narcotics', [NarcoticRegisterController::class, 'store'])->name('narcotics.store');

            // Phase 2 — KPI Dashboard
            Route::get('kpi', [\App\Http\Controllers\Pharmacy\KpiController::class, 'index'])->name('kpi.index');

            // Phase 2 — Medication Events (Pharmacovigilance)
            Route::get('medication-events', [\App\Http\Controllers\Pharmacy\MedicationEventController::class, 'index'])->name('medication-events.index');
            Route::get('medication-events/create', [\App\Http\Controllers\Pharmacy\MedicationEventController::class, 'create'])->name('medication-events.create');
            Route::post('medication-events', [\App\Http\Controllers\Pharmacy\MedicationEventController::class, 'store'])->name('medication-events.store');
            Route::get('medication-events/{medicationEvent}', [\App\Http\Controllers\Pharmacy\MedicationEventController::class, 'show'])->name('medication-events.show');
            Route::post('medication-events/{medicationEvent}/resolve', [\App\Http\Controllers\Pharmacy\MedicationEventController::class, 'resolve'])->name('medication-events.resolve');
            Route::post('medication-events/{medicationEvent}/assign', [\App\Http\Controllers\Pharmacy\MedicationEventController::class, 'assign'])->name('medication-events.assign');

            // Phase 2 — Pharmacy Documents
            Route::get('pharmacy-documents', [\App\Http\Controllers\Pharmacy\PharmacyDocumentController::class, 'index'])->name('pharmacy-documents.index');
            Route::get('pharmacy-documents/create', [\App\Http\Controllers\Pharmacy\PharmacyDocumentController::class, 'create'])->name('pharmacy-documents.create');
            Route::post('pharmacy-documents', [\App\Http\Controllers\Pharmacy\PharmacyDocumentController::class, 'store'])->name('pharmacy-documents.store');
            Route::get('pharmacy-documents/{pharmacyDocument}', [\App\Http\Controllers\Pharmacy\PharmacyDocumentController::class, 'show'])->name('pharmacy-documents.show');
            Route::get('pharmacy-documents/{pharmacyDocument}/edit', [\App\Http\Controllers\Pharmacy\PharmacyDocumentController::class, 'edit'])->name('pharmacy-documents.edit');
            Route::put('pharmacy-documents/{pharmacyDocument}', [\App\Http\Controllers\Pharmacy\PharmacyDocumentController::class, 'update'])->name('pharmacy-documents.update');
            Route::delete('pharmacy-documents/{pharmacyDocument}', [\App\Http\Controllers\Pharmacy\PharmacyDocumentController::class, 'destroy'])->name('pharmacy-documents.destroy');

            // Phase 3 — Stock Batches
            Route::get('batches', [\App\Http\Controllers\Pharmacy\BatchController::class, 'index'])->name('batches.index');
            Route::get('batches/{stockBatch}', [\App\Http\Controllers\Pharmacy\BatchController::class, 'show'])->name('batches.show');
            Route::post('batches/{stockBatch}/write-off', [\App\Http\Controllers\Pharmacy\BatchController::class, 'writeOff'])->name('batches.write-off');
            Route::post('batches/{stockBatch}/return-supplier', [\App\Http\Controllers\Pharmacy\BatchController::class, 'returnToSupplier'])->name('batches.return-supplier');

            // Phase 3 — Patient Medication Profile
            Route::get('patients/{patient}/medication-profile', \App\Http\Controllers\Pharmacy\PatientMedicationController::class)->name('patients.medication-profile');

            // Phase 3 — POS (Point of Sale)
            Route::get('pos', [PosController::class, 'index'])->name('pharmacy.pos.index');
            Route::get('pos/medicines/search', [PosController::class, 'searchMedicines'])->name('pharmacy.pos.medicines.search');
            Route::get('pos/patients/search', [PosController::class, 'searchPatients'])->name('pharmacy.pos.patients.search');
            Route::post('pos/cart/add', [PosController::class, 'addToCart'])->name('pharmacy.pos.cart.add');
            Route::post('pos/cart/remove', [PosController::class, 'removeFromCart'])->name('pharmacy.pos.cart.remove');
            Route::post('pos/cart/update', [PosController::class, 'updateCartItem'])->name('pharmacy.pos.cart.update');
            Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pharmacy.pos.checkout');
            Route::get('pos/receipt/{invoice}', [PosController::class, 'receipt'])->name('pharmacy.pos.receipt');

            // Phase 3 — Reports
            Route::get('reports/stock', [\App\Http\Controllers\Pharmacy\ReportController::class, 'stockReport'])->name('reports.stock');
            Route::get('reports/stock/pdf', [\App\Http\Controllers\Pharmacy\ReportController::class, 'stockReportPdf'])->name('reports.stock.pdf');
            Route::get('reports/stock/excel', [\App\Http\Controllers\Pharmacy\ReportController::class, 'stockReportExcel'])->name('reports.stock.excel');
            Route::get('reports/consumption', [\App\Http\Controllers\Pharmacy\ReportController::class, 'consumptionReport'])->name('reports.consumption');
            Route::get('reports/consumption/pdf', [\App\Http\Controllers\Pharmacy\ReportController::class, 'consumptionReportPdf'])->name('reports.consumption.pdf');
            Route::get('reports/consumption/excel', [\App\Http\Controllers\Pharmacy\ReportController::class, 'consumptionReportExcel'])->name('reports.consumption.excel');
        });

    // ── Hospitalization ───────────────────────────────────────
    Route::middleware('role:general_practitioner|specialist|nurse|administrator')
        ->group(function () {
            Route::resource('rooms', RoomController::class)->only(['index', 'create', 'store', 'show']);
            Route::resource('hospitalizations', HospitalizationController::class)->only(['index', 'create', 'store', 'show']);
            Route::post('hospitalizations/{hosp}/discharge', [HospitalizationController::class, 'discharge'])
                ->name('hospitalizations.discharge');
        });

    // ── Billing ───────────────────────────────────────────────
    Route::middleware('role:cashier|administrator')
        ->group(function () {
            Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);
            Route::resource('payments', PaymentController::class)->only(['index', 'store']);
            Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store']);
            Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])
                ->name('expenses.approve');
            Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])
                ->name('invoices.print');
        });

    // ── Administration ────────────────────────────────────────
    Route::middleware('role:administrator')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
            Route::resource('settings', SettingController::class)->only(['index', 'update']);
        });

    // ── Director (read-only) ──────────────────────────────────
    Route::middleware('role:director|administrator')
        ->group(function () {
            Route::get('reports', [\App\Http\Controllers\Billing\ExpenseController::class, 'reports'])
                ->name('reports.index');
        });
});
