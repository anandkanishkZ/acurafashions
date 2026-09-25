<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManualPaymentMethod;
use App\Models\Order;

class ManualPaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_all_manual_payment_methods'])->only('index');
        $this->middleware(['permission:add_manual_payment_method'])->only(['create', 'store']);
        $this->middleware(['permission:edit_manual_payment_method'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_manual_payment_method'])->only('destroy');
    }

    public function index()
    {
        $manual_payment_methods = ManualPaymentMethod::orderBy('id', 'desc')->paginate(15);
        return view('manual_payment_methods.index', compact('manual_payment_methods'));
    }

    public function create()
    {
        return view('manual_payment_methods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:bank,check,custom',
            'heading' => 'required|string|max:255',
        ]);

        $method = new ManualPaymentMethod;
        $this->fillFromRequest($method, $request);
        $method->save();

        flash(translate('Payment method added successfully'))->success();
        return redirect()->route('manual_payment_methods.index');
    }

    public function show($id)
    {
        return redirect()->route('manual_payment_methods.edit', $id);
    }

    public function edit($id)
    {
        $manual_payment_method = ManualPaymentMethod::findOrFail($id);
        return view('manual_payment_methods.edit', compact('manual_payment_method'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:bank,check,custom',
            'heading' => 'required|string|max:255',
        ]);

        $method = ManualPaymentMethod::findOrFail($id);
        $this->fillFromRequest($method, $request);
        $method->save();

        flash(translate('Payment method updated successfully'))->success();
        return redirect()->route('manual_payment_methods.index');
    }

    public function destroy($id)
    {
        $method = ManualPaymentMethod::findOrFail($id);
        $method->delete();

        flash(translate('Payment method deleted successfully'))->success();
        return redirect()->route('manual_payment_methods.index');
    }

    protected function fillFromRequest(ManualPaymentMethod $method, Request $request)
    {
        $method->type = $request->type;
        $method->heading = $request->heading;
        $method->status = $request->has('status') ? 1 : 0;

        if ($request->filled('photo')) {
            $method->photo = $request->photo;
        }

        if ($request->type === 'bank') {
            $bank_info = [];
            $bankNames = $request->input('bank_name', []);
            $accountNames = $request->input('account_name', []);
            $accountNumbers = $request->input('account_number', []);
            $routingNumbers = $request->input('routing_number', []);

            foreach ($bankNames as $key => $bankName) {
                if (empty($bankName)) {
                    continue;
                }
                $bank_info[] = [
                    'bank_name' => $bankName,
                    'account_name' => $accountNames[$key] ?? '',
                    'account_number' => $accountNumbers[$key] ?? '',
                    'routing_number' => $routingNumbers[$key] ?? '',
                ];
            }

            $method->bank_info = count($bank_info) > 0 ? json_encode($bank_info) : null;
            $method->description = $request->description;
        } else {
            $method->bank_info = null;
            $method->description = $request->description;
        }
    }

    /**
     * Named by routes/offline_payment.php as purchase_history.make_payment,
     * but the order re-payment modal actually submits to the already-working
     * CheckoutController::orderRePayment() (route('order.re_payment')), which
     * implements this exact same logic and correctly redirects with an
     * encrypted order id (purchase_history_details() expects decrypt($id)).
     * Kept as a working, correctly-behaving fallback for this named route
     * rather than left broken, in case anything else links to it directly.
     */
    public function submit_offline_payment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_option' => 'required|string',
            'trx_id' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        if (auth()->id() !== $order->user_id) {
            abort(403);
        }

        $data = [
            'name' => $request->payment_option,
            'amount' => $order->grand_total,
            'trx_id' => $request->trx_id,
            'photo' => $request->photo,
        ];

        $order->payment_type = $request->payment_option;
        $order->manual_payment = 1;
        $order->manual_payment_data = json_encode($data);
        $order->save();

        flash(translate('Payment submitted successfully. Please wait for admin approval.'))->success();
        return redirect()->route('purchase_history.details', encrypt($order->id));
    }

    /**
     * Renders the small "choose an offline method + trx id + photo" form
     * shown inside #offline_order_re_payment_modal on the customer order
     * details page (order_details_customer.blade.php's payment_modal('offline')).
     */
    public function offline_order_re_payment_modal(Request $request)
    {
        $order = Order::findOrFail($request->order_id);

        if (auth()->id() !== $order->user_id) {
            abort(403);
        }

        $manual_payment_methods = ManualPaymentMethod::where('status', 1)->get();

        return view('manual_payment_methods.offline_order_re_payment_form', compact('order', 'manual_payment_methods'));
    }

    // ------------------------------------------------------------------
    // Out of scope for this build (wallet recharge / package purchase
    // offline-payment modals) -- stubbed only so the shared routes in
    // routes/offline_payment.php resolve instead of fatal-erroring.
    // ------------------------------------------------------------------
    public function offline_recharge_modal(Request $request)
    {
        return '<div class="modal-body p-4 text-center text-muted">' . translate('This payment option is not available yet.') . '</div>';
    }

    public function offline_customer_package_purchase_modal(Request $request)
    {
        return '<div class="modal-body p-4 text-center text-muted">' . translate('This payment option is not available yet.') . '</div>';
    }

    public function offline_seller_package_purchase_modal(Request $request)
    {
        return '<div class="modal-body p-4 text-center text-muted">' . translate('This payment option is not available yet.') . '</div>';
    }
}
