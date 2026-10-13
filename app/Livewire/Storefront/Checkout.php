<?php

namespace App\Livewire\Storefront;

use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Checkout extends Component
{
    public array $cart = [];

    public float $subtotal = 0;

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

    public function mount(CartService $cartService): void
    {
        $this->loadCart($cartService);

        if (empty($this->cart)) {
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

    protected function loadCart(CartService $cartService): void
    {
        $this->cart = $cartService->get();
        $this->subtotal = $cartService->subtotal();
    }

    public function placeOrder(OrderService $orderService, CartService $cartService): void
    {
        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],

            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postalCode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
        ]);

        $cart = $cartService->get();

        if (empty($cart)) {
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

            $order = $orderService->createOrder(
                $customerData,
                $shippingAddress,
                $billingAddress,
                $cart
            );

            $cartService->clear();

            session()->flash(
                'order_success',
                "Order {$order->order_number} has been placed successfully."
            );

            $this->redirect(
                route('storefront.order-success', $order->order_number)
            );

        } catch (\RuntimeException $e) {

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
