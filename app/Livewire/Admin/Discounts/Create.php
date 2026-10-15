<?php

namespace App\Livewire\Admin\Discounts;

use App\Models\Discount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Create extends Component
{
    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $type = 'percentage';

    public $value = '';

    public $min_order_amount = '';

    public $max_discount_amount = '';

    public $usage_limit = '';

    public $usage_limit_per_customer = '';

    public $starts_at = '';

    public $expires_at = '';

    public bool $is_active = true;

    public function generateCode(): void
    {
        $prefix = strtoupper(Str::slug($this->name ?: 'SAVE', ''));
        $prefix = substr($prefix, 0, 6) ?: 'SAVE';
        $random = strtoupper(Str::random(4));
        $this->code = $prefix.'-'.$random;
    }

    public function updatedType(): void
    {
        if ($this->type === 'fixed' && ! empty($this->max_discount_amount)) {
            $this->max_discount_amount = '';
        }
    }

    protected function rules(): array
    {
        $valueRules = ['required', 'numeric', 'min:0.01'];
        if ($this->type === 'percentage') {
            $valueRules[] = 'max:100';
        }

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('discounts', 'code')->ignore($this->editingId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => $valueRules,
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'code' => 'coupon code',
            'min_order_amount' => 'minimum order amount',
            'max_discount_amount' => 'maximum discount amount',
            'usage_limit' => 'total usage limit',
            'usage_limit_per_customer' => 'usage limit per customer',
            'starts_at' => 'start date',
            'expires_at' => 'expiration date',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['description'] = ! empty($validated['description']) ? trim($validated['description']) : null;
        $validated['min_order_amount'] = ! empty($validated['min_order_amount']) ? (float) $validated['min_order_amount'] : null;
        $validated['max_discount_amount'] = (! empty($validated['max_discount_amount']) && $validated['type'] === 'percentage') ? (float) $validated['max_discount_amount'] : null;
        $validated['usage_limit'] = ! empty($validated['usage_limit']) ? (int) $validated['usage_limit'] : null;
        $validated['usage_limit_per_customer'] = ! empty($validated['usage_limit_per_customer']) ? (int) $validated['usage_limit_per_customer'] : null;
        $storeTimezone = (string) (store_setting('timezone') ?: config('app.timezone', 'UTC'));

        $validated['starts_at'] = ! empty($validated['starts_at'])
            ? Carbon::parse($validated['starts_at'], $storeTimezone)->setTimezone('UTC')
            : null;
        $validated['expires_at'] = ! empty($validated['expires_at'])
            ? Carbon::parse($validated['expires_at'], $storeTimezone)->setTimezone('UTC')
            : null;

        if ($this->editingId) {
            $discount = Discount::findOrFail($this->editingId);
            $discount->update($validated);
            session()->flash('success', "Discount '{$discount->code}' updated successfully.");
        } else {
            $discount = Discount::create($validated);
            session()->flash('success', "Discount '{$discount->code}' created successfully.");
        }

        return redirect()->route('admin.discounts.index');
    }

    public function render()
    {
        return view('livewire.admin.discounts.form', [
            'isEditing' => (bool) $this->editingId,
        ]);
    }
}
