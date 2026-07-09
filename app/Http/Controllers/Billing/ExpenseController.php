<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $expenses = Expense::with(['recordedBy', 'approvedBy'])
            ->latest('expense_date')
            ->paginate(20);

        return view('billing.expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        return view('billing.expenses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category'       => 'required|in:salary,supplies,medicine,maintenance,utilities,other',
            'description'    => 'required|string|min:5|max:500',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,other',
            'expense_date'   => 'required|date',
            'reference_code' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        Expense::create([
            ...$validated,
            'recorded_by_id' => auth()->id(),
            'status'         => 'pending',
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Dépense enregistrée.');
    }

    public function approve(Expense $expense): RedirectResponse
    {
        $expense->approve(auth()->user());
        return back()->with('success', 'Dépense approuvée.');
    }

    public function reports(): View
    {
        $monthlyRevenue  = \App\Models\Payment::whereMonth('paid_at', now()->month)->sum('amount');
        $monthlyExpenses = Expense::whereMonth('expense_date', now()->month)
            ->where('status', 'approved')->sum('amount');

        $revenueByMethod = \App\Models\Payment::today()
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $expensesByCategory = Expense::whereMonth('expense_date', now()->month)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('reports.dashboard', compact(
            'monthlyRevenue', 'monthlyExpenses',
            'revenueByMethod', 'expensesByCategory'
        ));
    }
}
