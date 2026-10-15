<?php

namespace App\Livewire\Admin\Discounts;

use App\Models\Discount;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Edit extends Create
{
    public Discount $discount;

    public function mount(Discount $discount): void
    {
        $this->discount = $discount;
        $this->editingId = $discount->id;
        $this->code = $discount->code;
        $this->name = $discount->name;
        $this->description = $discount->description ?? '';
        $this->type = $discount->type;
        $this->value = (string) $discount->value;
        $this->min_order_amount = $discount->min_order_amount ? (string) $discount->min_order_amount : '';
        $this->max_discount_amount = $discount->max_discount_amount ? (string) $discount->max_discount_amount : '';
        $this->usage_limit = $discount->usage_limit ? (string) $discount->usage_limit : '';
        $this->usage_limit_per_customer = $discount->usage_limit_per_customer ? (string) $discount->usage_limit_per_customer : '';
        $storeTimezone = (string) (store_setting('timezone') ?: config('app.timezone', 'UTC'));

        $this->starts_at = $discount->starts_at ? $discount->starts_at->setTimezone($storeTimezone)->format('Y-m-d\TH:i') : '';
        $this->expires_at = $discount->expires_at ? $discount->expires_at->setTimezone($storeTimezone)->format('Y-m-d\TH:i') : '';
        $this->is_active = (bool) $discount->is_active;
    }
}
