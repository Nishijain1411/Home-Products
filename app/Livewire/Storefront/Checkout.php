<?php

namespace App\Livewire\Storefront;

use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Checkout extends Component
{
    public array $cart = [];

    public float $subtotal = 0;

    public float $shippingFee = 0.0;

    public float $taxAmount = 0.0;

    public float $taxRate = 0.0;

    public string $taxName = 'Tax';

    public bool $taxInclusive = false;

    public float $total = 0.0;

    public string $couponCode = '';

    public ?array $appliedCoupon = null;

    public float $discountAmount = 0.0;

    public bool $enableCod = true;

    public string $codInstructions = '';

    public bool $enableCard = true;

    public bool $enableUpi = true;

    public string $upiInstructions = '';

    public string $upiVpa = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $city = '';

    public string $state = '';

    public string $postalCode = '';

    public string $country = 'India';

    public bool $billingSameAsShipping = true;

    public string $paymentMethod = 'cod';

    public string $cardName = '';

    public string $cardNumber = '';

    public string $cardExpiry = '';

    public string $cardCvv = '';

    public string $upiId = '';

    public ?string $gatewayTransactionId = null;

    public ?string $gatewaySignature = null;

    public ?string $gatewayOrderId = null;

    public function getEnabledPaymentMethods(): array
    {
        $methods = [];
        if ($this->enableCod) {
            $methods[] = 'cod';
        }
        if ($this->enableCard) {
            $methods[] = 'card';
        }
        if ($this->enableUpi) {
            $methods[] = 'upi';
        }
        $methods[] = 'razorpay';
        $methods[] = 'gateway';
        $methods[] = 'stripe';

        return $methods;
    }

    protected function rules(): array
    {
        $enabled = $this->getEnabledPaymentMethods();
        $inMethods = ! empty($enabled) ? implode(',', $enabled) : 'cod,card,upi';

        $rules = [
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],

            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postalCode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'paymentMethod' => ['required', 'string', 'in:'.$inMethods],
        ];

        if ($this->paymentMethod === 'card') {
            $rules['cardName'] = ['required', 'string', 'max:100'];
            $rules['cardNumber'] = ['required', 'string', 'min:15', 'max:19'];
            $rules['cardExpiry'] = ['required', 'string', 'regex:/^(0[1-9]|1[0-2])\/?([0-9]{2})$/'];
            $rules['cardCvv'] = ['required', 'string', 'min:3', 'max:4'];
        }

        if ($this->paymentMethod === 'upi') {
            $rules['upiId'] = ['required', 'string', 'regex:/^[\w\.\-]+@[\w\-]+$/'];
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'firstName' => 'first name',
            'lastName' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'address' => 'street address',
            'city' => 'city',
            'state' => 'state',
            'postalCode' => 'postal code',
            'country' => 'country',
            'paymentMethod' => 'payment method',
            'cardName' => 'cardholder name',
            'cardNumber' => 'card number',
            'cardExpiry' => 'card expiry date (MM/YY)',
            'cardCvv' => 'card CVV',
            'upiId' => 'UPI ID',
        ];
    }

    protected function messages(): array
    {
        return [
            'cardExpiry.regex' => 'The expiration date must be in MM/YY format.',
            'upiId.regex' => 'The UPI ID format is invalid (e.g. username@bank).',
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function mount(CartService $cartService, SettingService $settingService): void
    {
        $guestCheckout = (bool) $settingService->get('guest_checkout', true);
        if (! $guestCheckout && ! Auth::check()) {
            session()->flash('cart_error', 'Guest checkout is disabled. Please log in or create an account to proceed.');
            $this->redirectRoute('login');

            return;
        }

        $this->loadSettings($settingService);
        $this->loadCart($cartService, $settingService);

        if (empty($this->cart)) {
            $this->redirectRoute('storefront.cart');

            return;
        }

        $stockValidation = $cartService->validateStock();
        if ($stockValidation['has_issues']) {
            session()->flash('cart_error', 'Some items in your cart have stock limitations. Please adjust before checkout.');
            $this->redirectRoute('storefront.cart');

            return;
        }

        if (Auth::check()) {
            $user = Auth::user();

            $nameParts = preg_split('/\s+/', trim($user->name), 2);

            $this->firstName = $nameParts[0] ?? '';
            $this->lastName = $nameParts[1] ?? '';
            $this->email = $user->email;
        }
    }

    protected function loadSettings(SettingService $settingService): void
    {
        $this->enableCod = (bool) $settingService->get('enable_cod', true);
        $this->codInstructions = (string) $settingService->get('cod_instructions', 'Pay in cash when order is delivered.');
        $this->enableCard = (bool) $settingService->get('enable_card', true);
        $this->enableUpi = (bool) $settingService->get('enable_upi', true);
        $this->upiInstructions = (string) $settingService->get('upi_instructions', 'Pay instantly via UPI.');
        $this->upiVpa = (string) $settingService->get('upi_merchant_vpa', '');

        $enabled = $this->getEnabledPaymentMethods();
        if (! in_array($this->paymentMethod, $enabled, true) && ! empty($enabled)) {
            $this->paymentMethod = $enabled[0];
        }
    }

    public function applyCoupon(DiscountService $discountService, SettingService $settingService): void
    {
        $this->resetErrorBag('couponCode');
        $code = trim($this->couponCode);

        if ($code === '') {
            $this->addError('couponCode', 'Please enter a coupon code.');

            return;
        }

        $validation = $discountService->validateCode(
            $code,
            $this->subtotal,
            Auth::id(),
            $this->email ?: Auth::user()?->email
        );

        if (! $validation['valid']) {
            $this->addError('couponCode', $validation['message']);

            return;
        }

        $discountService->setAppliedCoupon($validation['discount'], $validation['discount_amount']);
        $this->couponCode = '';
        $this->loadCart(app(CartService::class), $settingService);
        session()->flash('coupon_success', "Coupon '{$validation['discount']->code}' applied successfully!");
    }

    public function removeCoupon(DiscountService $discountService, SettingService $settingService): void
    {
        $discountService->removeAppliedCoupon();
        $this->loadCart(app(CartService::class), $settingService);
        session()->flash('coupon_success', 'Coupon removed.');
    }

    protected function loadCart(CartService $cartService, ?SettingService $settingService = null): void
    {
        $settingService ??= app(SettingService::class);
        $discountService = app(DiscountService::class);

        $this->cart = $cartService->get();
        $this->subtotal = $cartService->subtotal();

        $this->appliedCoupon = $discountService->getAppliedCoupon();
        $this->discountAmount = 0.0;

        if ($this->appliedCoupon) {
            $validation = $discountService->validateCode(
                $this->appliedCoupon['code'],
                $this->subtotal,
                Auth::id(),
                $this->email ?: Auth::user()?->email
            );

            if ($validation['valid']) {
                $this->discountAmount = $validation['discount_amount'];
                $discountService->setAppliedCoupon($validation['discount'], $this->discountAmount);
                $this->appliedCoupon = $discountService->getAppliedCoupon();
            } else {
                $discountService->removeAppliedCoupon();
                $this->appliedCoupon = null;
                session()->flash('cart_error', 'Applied coupon was removed: '.$validation['message']);
            }
        }

        $totals = $settingService->calculateTotals($this->subtotal, $this->discountAmount);
        $this->shippingFee = $totals['shipping'];
        $this->taxAmount = $totals['tax'];
        $this->taxName = $totals['tax_name'];
        $this->taxRate = $totals['tax_rate'];
        $this->taxInclusive = $totals['tax_inclusive'];
        $this->total = $totals['total'];
    }

    public function placeOrder(OrderService $orderService, CartService $cartService, PaymentService $paymentService): void
    {
        $validated = $this->validate();

        $cart = $cartService->get();

        if (empty($cart)) {
            $this->redirectRoute('storefront.cart');

            return;
        }

        $stockValidation = $cartService->validateStock();
        if ($stockValidation['has_issues']) {
            session()->flash('cart_error', 'Inventory has changed. Please adjust your cart before placing your order.');
            $this->redirectRoute('storefront.cart');

            return;
        }

        $customerData = [
            'customer_id' => Auth::id(),
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ];

        $shippingAddress = [
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postalCode'],
            'country' => $validated['country'],
            'phone' => $validated['phone'],
        ];

        $billingAddress = $this->billingSameAsShipping
            ? $shippingAddress
            : $shippingAddress;

        try {
            $paymentDetails = match ($this->paymentMethod) {
                'card' => [
                    'cardName' => $this->cardName,
                    'cardNumber' => $this->cardNumber,
                    'cardExpiry' => $this->cardExpiry,
                    'cardCvv' => $this->cardCvv,
                ],
                'upi' => [
                    'upiId' => $this->upiId,
                ],
                'gateway', 'razorpay', 'stripe' => [
                    'transaction_id' => $this->gatewayTransactionId,
                    'razorpay_payment_id' => $this->gatewayTransactionId,
                    'razorpay_order_id' => $this->gatewayOrderId,
                    'razorpay_signature' => $this->gatewaySignature,
                    'signature' => $this->gatewaySignature,
                ],
                default => [],
            };

            $paymentResult = $paymentService->process(
                $this->paymentMethod,
                $this->total,
                $paymentDetails
            );

            $order = $orderService->createOrder(
                $customerData,
                $shippingAddress,
                $billingAddress,
                $cart,
                $paymentResult,
                $this->appliedCoupon['code'] ?? null
            );

            $cartService->clear();

            session()->flash(
                'order_success',
                "Order {$order->order_number} has been placed successfully."
            );

            $this->redirect(
                route('storefront.order-success', $order->order_number)
            );

        } catch (\Throwable $e) {
            $this->addError(
                'checkout',
                $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view('livewire.storefront.checkout')->layout('storefront.layouts.app');
    }
}
