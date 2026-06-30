<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Stock;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $stats = match (true) {
            $user->hasRole('administrator') || $user->hasRole('director') => $this->adminStats(),
            $user->hasRole('general_practitioner') || $user->hasRole('specialist') => $this->doctorStats(),
            $user->hasRole('pharmacist') || $user->hasRole('preparateur') => $this->pharmacistStats(),
            $user->hasRole('stock_manager') => $this->stockManagerStats(),
            $user->hasRole('cashier') => $this->cashierStats(),
            $user->hasRole('receptionist') => $this->receptionistStats(),
            default => [],
        };

        return view('dashboard.index', compact('stats'));
    }

    private function adminStats(): array
    {
        return [
            'total_patients'       => Patient::active()->count(),
            'today_appointments'   => Appointment::today()->count(),
            'today_revenue'        => Payment::today()->sum('amount'),
            'low_stock_count'      => Stock::lowStock()->count(),
            'overdue_invoices'     => Invoice::overdue()->count(),
            'monthly_revenue'      => Payment::whereMonth('paid_at', now()->month)->sum('amount'),
        ];
    }

    private function doctorStats(): array
    {
        $doctorId = auth()->id();
        return [
            'today_appointments'   => Appointment::today()->byDoctor($doctorId)->count(),
            'open_consultations'   => \App\Models\Consultation::open()->byDoctor($doctorId)->count(),
            'pending_lab_results'  => \App\Models\LabRequest::where('doctor_id', $doctorId)->where('status','pending')->count(),
            'upcoming_appointments'=> Appointment::upcoming()->byDoctor($doctorId)->take(5)->with('patient')->get(),
        ];
    }

    private function pharmacistStats(): array
    {
        return [
            'low_stock_medicines'  => Stock::with('medicine')->lowStock()->get(),
            'pending_prescriptions'=> \App\Models\Prescription::pending()->count(),
            'pending_orders'       => \App\Models\PurchaseOrder::pending()->count(),
            'today_dispensations'  => \App\Models\Dispensation::whereDate('dispensed_at', today())->count(),
        ];
    }

    private function stockManagerStats(): array
    {
        return [
            'low_stock_medicines'  => Stock::with('medicine')->lowStock()->get(),
            'pending_orders'       => \App\Models\PurchaseOrder::pending()->count(),
            'expiring_batches'     => \App\Models\StockBatch::whereBetween('expiry_date', [now(), now()->addMonths(3)])->count(),
        ];
    }

    private function cashierStats(): array
    {
        return [
            'today_revenue'        => Payment::today()->sum('amount'),
            'today_payments'       => Payment::today()->count(),
            'unpaid_invoices'      => Invoice::unpaid()->count(),
            'overdue_invoices'     => Invoice::overdue()->count(),
        ];
    }

    private function receptionistStats(): array
    {
        return [
            'today_appointments'   => Appointment::today()->count(),
            'waiting_queue'        => \App\Models\Queue::waiting()->count(),
            'today_new_patients'   => Patient::whereDate('created_at', today())->count(),
        ];
    }
}
