<?php

namespace App\Livewire\Storefront;

use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Cart extends Component
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

    public bool $hasStockIssues = false;

    public array $stockIssues = [];

    public function mount(CartService $cartService, SettingService $settingService): void
    {
        $this->loadCart($cartService, $settingService);
    }

    public function updateQuantity(int $variantId, int $quantity, CartService $cartService, SettingService $settingService): void
    {
        $this->validate([
            'cart' => 'array',
        ]);

        try {
            $cartService->updateQuantity($variantId, $quantity);
        } catch (\Throwable $e) {
            session()->flash('cart_error', $e->getMessage());
        }

        $this->loadCart($cartService, $settingService);
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $variantId, CartService $cartService, SettingService $settingService): void
    {
        $cartService->remove($variantId);

        $this->loadCart($cartService, $settingService);
        $this->dispatch('cart-updated');
    }

    public function clearCart(CartService $cartService, SettingService $settingService): void
    {
        $cartService->clear();

        $this->loadCart($cartService, $settingService);
        $this->dispatch('cart-updated');
    }

    public function autoAdjustStock(CartService $cartService, SettingService $settingService): void
    {
        $adjusted = $cartService->autoAdjustCartStock();

        $this->loadCart($cartService, $settingService);
        $this->dispatch('cart-updated');

        if (! empty($adjusted)) {
            session()->flash('cart_success', implode(' ', $adjusted));
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
            Auth::user()?->email,
            $this->cart
        );

        if (! $validation['valid']) {
            $this->addError('couponCode', $validation['message']);

            return;
        }

        $discountService->setAppliedCoupon($validation['discount'], $validation['discount_amount']);
        $this->couponCode = '';
        $this->loadCart(app(CartService::class), $settingService);
        session()->flash('cart_success', "Coupon '{$validation['discount']->code}' applied successfully!");
    }

    public function removeCoupon(DiscountService $discountService, SettingService $settingService): void
    {
        $discountService->removeAppliedCoupon();
        $this->loadCart(app(CartService::class), $settingService);
        session()->flash('cart_success', 'Coupon removed.');
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
                Auth::user()?->email,
                $this->cart
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

        $validation = $cartService->validateStock();
        $this->hasStockIssues = $validation['has_issues'];
        $this->stockIssues = $validation['issues'];
    }

    public function render()
    {
        return view('livewire.storefront.cart')->layout('storefront.layouts.app');
    }
}
