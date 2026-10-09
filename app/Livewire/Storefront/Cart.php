<?php

namespace App\Livewire\Storefront;
use App\Services\CartService;
use Livewire\Component;

class Cart extends Component
{
    public array $cart = [];
    public float $subtotal = 0;

    public function mount(CartService $cartService): void
    {
        $this->loadCart($cartService);
    }
    public function updateQuantity(int $variantId,int $quantity,CartService $cartService): void 
    {
        $this->validate([
            'cart' => 'array',
        ]);

        $cartService->updateQuantity($variantId, $quantity);

        $this->loadCart($cartService);
    }
    public function removeItem(int $variantId,CartService $cartService): void 
    {
        $cartService->remove($variantId);

        $this->loadCart($cartService);
    }
    public function clearCart(CartService $cartService): void
    {
        $cartService->clear();

        $this->loadCart($cartService);
    }

    protected function loadCart(CartService $cartService): void
    {
        $this->cart = $cartService->get();
        $this->subtotal = $cartService->subtotal();
    }
    public function render()
    {
        return view('livewire.storefront.cart')->layout('storefront.layouts.app');
    }
}
