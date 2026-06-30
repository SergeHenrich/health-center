<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Patient;
use App\Services\Pharmacy\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PosController extends Controller
{
    private const CART_KEY = 'pharmacy_pos_cart';

    public function __construct(private readonly PosService $posService) {}

    public function index(): View
    {
        $cart = session()->get(self::CART_KEY, []);
        $cartTotal = $this->getCartTotal($cart);

        return view('pharmacy.pos.index', compact('cart', 'cartTotal'));
    }

    public function searchMedicines(Request $request): JsonResponse
    {
        $term = $request->get('q', '');

        $medicines = Medicine::with('stock')
            ->active()
            ->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                  ->orWhere('generic_name', 'ilike', "%{$term}%")
                  ->orWhere('code', 'ilike', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id'                     => $m->id,
                'code'                   => $m->code,
                'name'                   => $m->name,
                'generic_name'           => $m->generic_name,
                'form'                   => $m->form,
                'strength'               => $m->strength,
                'unit_price'             => (float) $m->unit_price,
                'stock_available'        => $m->getCurrentStock(),
                'requires_prescription'  => $m->requires_prescription,
            ]);

        return response()->json($medicines);
    }

    public function searchPatients(Request $request): JsonResponse
    {
        $term = $request->get('q', '');

        $patients = Patient::active()
            ->where(function ($q) use ($term) {
                $q->where('first_name', 'ilike', "%{$term}%")
                  ->orWhere('last_name', 'ilike', "%{$term}%")
                  ->orWhere('patient_code', 'ilike', "%{$term}%")
                  ->orWhere('phone', 'ilike', "%{$term}%");
            })
            ->orderBy('last_name')
            ->limit(15)
            ->get()
            ->map(fn($p) => [
                'id'        => $p->id,
                'full_name' => $p->getFullName(),
                'code'      => $p->patient_code,
                'phone'     => $p->phone,
            ]);

        return response()->json($patients);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity'    => 'required|integer|min:1',
        ]);

        $medicine = Medicine::with('stock')->findOrFail($validated['medicine_id']);

        if ($medicine->getCurrentStock() < $validated['quantity']) {
            return response()->json([
                'error' => "Stock insuffisant pour {$medicine->name}. Disponible: {$medicine->getCurrentStock()}",
            ], 422);
        }

        $cart = session()->get(self::CART_KEY, []);

        if (isset($cart[$medicine->id])) {
            $newQty = $cart[$medicine->id]['quantity'] + $validated['quantity'];
            if ($medicine->getCurrentStock() < $newQty) {
                return response()->json([
                    'error' => "Stock insuffisant pour {$medicine->name}. Disponible: {$medicine->getCurrentStock()}",
                ], 422);
            }
            $cart[$medicine->id]['quantity'] = $newQty;
        } else {
            $cart[$medicine->id] = [
                'medicine_id'            => $medicine->id,
                'name'                   => $medicine->name,
                'form'                   => $medicine->form,
                'strength'               => $medicine->strength,
                'unit_price'             => (float) $medicine->unit_price,
                'quantity'               => $validated['quantity'],
                'requires_prescription'  => $medicine->requires_prescription,
            ];
        }

        session()->put(self::CART_KEY, $cart);

        return $this->cartResponse($cart);
    }

    public function removeFromCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
        ]);

        $cart = session()->get(self::CART_KEY, []);
        unset($cart[$validated['medicine_id']]);
        session()->put(self::CART_KEY, $cart);

        return $this->cartResponse($cart);
    }

    public function updateCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity'    => 'required|integer|min:0',
        ]);

        $cart = session()->get(self::CART_KEY, []);

        if ($validated['quantity'] === 0) {
            unset($cart[$validated['medicine_id']]);
        } else {
            $medicine = Medicine::with('stock')->find($validated['medicine_id']);
            if ($medicine && $medicine->getCurrentStock() < $validated['quantity']) {
                return response()->json([
                    'error' => "Stock insuffisant. Disponible: {$medicine->getCurrentStock()}",
                ], 422);
            }
            $cart[$validated['medicine_id']]['quantity'] = $validated['quantity'];
        }

        session()->put(self::CART_KEY, $cart);

        return $this->cartResponse($cart);
    }

    public function checkout(Request $request): RedirectResponse
    {
        Gate::authorize('pharmacy.pos.sell');

        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'method'     => 'required|in:cash,mobile_money,bank_transfer,card,insurance,other',
            'amount'     => 'required|numeric|min:0.01',
            'reference_code' => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:500',
        ]);

        $cart = session()->get(self::CART_KEY, []);

        try {
            $result = $this->posService->checkout(
                $cart,
                $validated,
                $validated['patient_id'],
            );

            session()->forget(self::CART_KEY);

            return redirect()
                ->route('pharmacy.pos.receipt', $result['invoice'])
                ->with('success', 'Vente effectuée avec succès.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la vente : ' . $e->getMessage());
        }
    }

    public function receipt(\App\Models\Invoice $invoice): View
    {
        $invoice->load(['patient', 'items', 'payments.receivedBy', 'createdBy']);
        return view('pharmacy.pos.receipt', compact('invoice'));
    }

    private function getCartTotal(array $cart): float
    {
        return collect($cart)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
    }

    private function cartResponse(array $cart): JsonResponse
    {
        return response()->json([
            'cart'       => array_values($cart),
            'cart_count' => count($cart),
            'cart_total' => $this->getCartTotal($cart),
        ]);
    }
}
