<?php

namespace App\Livewire\Admin\Discounts;

use App\Models\Category;
use App\Models\CustomerSegment;
use App\Models\Discount;
use App\Models\Product;
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

    public string $customer_eligibility = 'all'; // 'all', 'segments'

    public array $selected_segments = [];

    public string $applies_to = 'all'; // 'all', 'categories', 'products'

    public array $selected_categories = [];

    public array $selected_products = [];

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
            'customer_eligibility' => ['required', 'in:all,segments'],
            'selected_segments' => [
                'array',
                Rule::requiredIf(fn () => $this->customer_eligibility === 'segments'),
                function ($attribute, $value, $fail) {
                    if ($this->customer_eligibility === 'segments' && empty($value)) {
                        $fail('Please select at least one customer segment.');
                    }
                },
            ],
            'selected_segments.*' => ['integer', 'exists:customer_segments,id'],
            'applies_to' => ['required', 'in:all,categories,products'],
            'selected_categories' => [
                'array',
                Rule::requiredIf(fn () => $this->applies_to === 'categories'),
                function ($attribute, $value, $fail) {
                    if ($this->applies_to === 'categories' && empty($value)) {
                        $fail('Please select at least one category.');
                    }
                },
            ],
            'selected_categories.*' => ['integer', 'exists:categories,id'],
            'selected_products' => [
                'array',
                Rule::requiredIf(fn () => $this->applies_to === 'products'),
                function ($attribute, $value, $fail) {
                    if ($this->applies_to === 'products' && empty($value)) {
                        $fail('Please select at least one product.');
                    }
                },
            ],
            'selected_products.*' => ['integer', 'exists:products,id'],
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
            'customer_eligibility' => 'customer eligibility',
            'selected_segments' => 'customer segments',
            'applies_to' => 'applies to',
            'selected_categories' => 'categories',
            'selected_products' => 'products',
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
        $validated['customer_eligibility'] = $this->customer_eligibility;
        $validated['applies_to'] = $this->applies_to;
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

            // Sync targeting relationships
            $discount->customerSegments()->sync($this->customer_eligibility === 'segments' ? $this->selected_segments : []);
            $discount->categories()->sync($this->applies_to === 'categories' ? $this->selected_categories : []);
            $discount->products()->sync($this->applies_to === 'products' ? $this->selected_products : []);

            session()->flash('success', "Discount '{$discount->code}' updated successfully.");
        } else {
            $discount = Discount::create($validated);

            // Sync targeting relationships
            $discount->customerSegments()->sync($this->customer_eligibility === 'segments' ? $this->selected_segments : []);
            $discount->categories()->sync($this->applies_to === 'categories' ? $this->selected_categories : []);
            $discount->products()->sync($this->applies_to === 'products' ? $this->selected_products : []);

            session()->flash('success', "Discount '{$discount->code}' created successfully.");
        }

        return redirect()->route('admin.discounts.index');
    }

    public function render()
    {
        $availableSegments = CustomerSegment::where('is_active', true)->orderBy('name')->get();
        $availableCategories = Category::where('is_active', true)->orderBy('name')->get();
        $availableProducts = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']);

        return view('livewire.admin.discounts.form', [
            'isEditing' => (bool) $this->editingId,
            'availableSegments' => $availableSegments,
            'availableCategories' => $availableCategories,
            'availableProducts' => $availableProducts,
        ]);
    }
}
