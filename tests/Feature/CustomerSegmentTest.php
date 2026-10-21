<?php

use App\Livewire\Admin\Customers\Index as AdminCustomerIndex;
use App\Livewire\Admin\CustomerSegments\Index as AdminCustomerSegmentsIndex;
use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Store Administrator', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
});

test('store administrator can access customer segments page', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $this->get(route('admin.customer-segments.index'))
        ->assertOk()
        ->assertSee('Customer Segments');
});

test('unauthorized customer cannot access customer segments page', function () {
    $customer = User::factory()->create();
    $customer->assignRole('Customer');
    $this->actingAs($customer);

    $this->get(route('admin.customer-segments.index'))
        ->assertForbidden();
});

test('administrator can create, edit, and delete customer segments', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    // Create segment
    $component = Livewire::test(AdminCustomerSegmentsIndex::class)
        ->call('openCreateSegment')
        ->assertSet('showSegmentModal', true)
        ->set('name', 'VIP Shoppers')
        ->set('slug', 'vip-shoppers')
        ->set('description', 'High lifetime spenders')
        ->set('color', 'purple')
        ->call('saveSegment')
        ->assertSet('showSegmentModal', false);

    expect($component->get('successMessage'))->toContain('created successfully');
    $segment = CustomerSegment::where('slug', 'vip-shoppers')->first();
    expect($segment)->not->toBeNull();
    expect($segment->name)->toBe('VIP Shoppers');
    expect($segment->color)->toBe('purple');

    // Edit segment
    $component->call('openEditSegment', $segment->id)
        ->assertSet('showSegmentModal', true)
        ->assertSet('name', 'VIP Shoppers')
        ->set('name', 'Platinum VIP')
        ->call('saveSegment')
        ->assertSet('showSegmentModal', false);

    expect($component->get('successMessage'))->toContain('updated successfully');
    expect($segment->fresh()->name)->toBe('Platinum VIP');

    // Delete segment
    $component->call('confirmDeleteSegment', $segment->id)
        ->assertSet('showDeleteModal', true)
        ->call('deleteSegment')
        ->assertSet('showDeleteModal', false);

    expect(CustomerSegment::find($segment->id))->toBeNull();
});

test('administrator can bulk add customers to a segment', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $segment = CustomerSegment::create([
        'name' => 'Loyal Customers',
        'slug' => 'loyal-customers',
        'color' => 'blue',
        'is_active' => true,
    ]);

    $cust1 = User::factory()->create(['name' => 'Alice Customer', 'email' => 'alice@example.com']);
    $cust2 = User::factory()->create(['name' => 'Bob Customer', 'email' => 'bob@example.com']);
    $cust3 = User::factory()->create(['name' => 'Charlie Customer', 'email' => 'charlie@example.com']);

    $component = Livewire::test(AdminCustomerSegmentsIndex::class)
        ->call('viewSegmentMembers', $segment->id)
        ->assertSet('activeSegmentId', $segment->id)
        ->call('openAddCustomersModal')
        ->assertSet('showAddCustomersModal', true)
        ->set('selectedCustomers', [(string) $cust1->id, (string) $cust2->id])
        ->set('assignmentMode', 'add')
        ->call('assignCustomersToSegment')
        ->assertSet('showAddCustomersModal', false)
        ->assertSet('selectedCustomers', []);

    expect($component->get('successMessage'))->toContain('Added 2 customer(s)');
    expect($segment->fresh()->customers->pluck('id')->all())->toEqualCanonicalizing([$cust1->id, $cust2->id]);
    expect($segment->fresh()->customers->pluck('id')->all())->not->toContain($cust3->id);
});

test('administrator can move customers exclusively to a segment in bulk (move mode)', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $oldSegment = CustomerSegment::create(['name' => 'Silver Tier', 'slug' => 'silver-tier', 'color' => 'blue']);
    $newSegment = CustomerSegment::create(['name' => 'Gold Tier', 'slug' => 'gold-tier', 'color' => 'amber']);

    $cust = User::factory()->create(['name' => 'David Customer', 'email' => 'david@example.com']);
    $cust->segments()->attach($oldSegment->id);

    expect($cust->fresh()->segments->pluck('id')->all())->toContain($oldSegment->id);

    // Now move customer to new segment
    Livewire::test(AdminCustomerSegmentsIndex::class)
        ->call('viewSegmentMembers', $newSegment->id)
        ->call('openAddCustomersModal')
        ->set('selectedCustomers', [(string) $cust->id])
        ->set('assignmentMode', 'move')
        ->call('assignCustomersToSegment');

    // Customer is now exclusively in Gold Tier, not Silver Tier
    expect($cust->fresh()->segments->pluck('id')->all())->toEqualCanonicalizing([$newSegment->id]);
    expect($cust->fresh()->segments->pluck('id')->all())->not->toContain($oldSegment->id);
});

test('administrator can remove single and bulk customers from a segment', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $segment = CustomerSegment::create(['name' => 'Test Segment', 'slug' => 'test-segment', 'color' => 'emerald']);

    $cust1 = User::factory()->create(['name' => 'User One']);
    $cust2 = User::factory()->create(['name' => 'User Two']);
    $cust3 = User::factory()->create(['name' => 'User Three']);

    $segment->customers()->attach([$cust1->id, $cust2->id, $cust3->id]);
    expect($segment->fresh()->customers)->toHaveCount(3);

    // Remove single member
    Livewire::test(AdminCustomerSegmentsIndex::class)
        ->call('viewSegmentMembers', $segment->id)
        ->call('removeMember', $cust1->id);

    expect($segment->fresh()->customers->pluck('id')->all())->toEqualCanonicalizing([$cust2->id, $cust3->id]);

    // Bulk remove remaining members
    Livewire::test(AdminCustomerSegmentsIndex::class)
        ->call('viewSegmentMembers', $segment->id)
        ->set('selectedMembers', [(string) $cust2->id, (string) $cust3->id])
        ->call('removeSelectedMembers');

    expect($segment->fresh()->customers)->toHaveCount(0);
});

test('customers index allows bulk selecting customers and assigning them to a segment', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $segment = CustomerSegment::create(['name' => 'Newsletter VIP', 'slug' => 'newsletter-vip', 'color' => 'rose']);

    $c1 = User::factory()->create(['name' => 'Emma Watson', 'email' => 'emma@example.com']);
    $c2 = User::factory()->create(['name' => 'Liam Neeson', 'email' => 'liam@example.com']);

    $component = Livewire::test(AdminCustomerIndex::class)
        ->set('selected', [(string) $c1->id, (string) $c2->id])
        ->call('openBulkSegmentModal')
        ->assertSet('showBulkSegmentModal', true)
        ->set('selectedSegmentId', (string) $segment->id)
        ->set('bulkMode', 'add')
        ->call('bulkAssignSegment')
        ->assertSet('showBulkSegmentModal', false)
        ->assertSet('selected', []);

    expect($component->get('successMessage'))->toContain('Added 2 customer(s)');
    expect($c1->fresh()->segments->pluck('id')->all())->toContain($segment->id);
    expect($c2->fresh()->segments->pluck('id')->all())->toContain($segment->id);
});

test('customers index can filter by segment', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $segmentA = CustomerSegment::create(['name' => 'Segment Alpha', 'slug' => 'seg-alpha', 'color' => 'blue']);
    $segmentB = CustomerSegment::create(['name' => 'Segment Beta', 'slug' => 'seg-beta', 'color' => 'purple']);

    $custA = User::factory()->create(['name' => 'Alpha Member', 'email' => 'alpha@example.com']);
    $custA->segments()->attach($segmentA->id);

    $custB = User::factory()->create(['name' => 'Beta Member', 'email' => 'beta@example.com']);
    $custB->segments()->attach($segmentB->id);

    $unsegmented = User::factory()->create(['name' => 'Unassigned Person', 'email' => 'unassigned@example.com']);

    // Filter by Alpha
    Livewire::test(AdminCustomerIndex::class)
        ->set('segmentFilter', (string) $segmentA->id)
        ->assertSee('Alpha Member')
        ->assertDontSee('Beta Member')
        ->assertDontSee('Unassigned Person');

    // Filter unsegmented
    Livewire::test(AdminCustomerIndex::class)
        ->set('segmentFilter', 'unsegmented')
        ->assertDontSee('Alpha Member')
        ->assertDontSee('Beta Member')
        ->assertSee('Unassigned Person');

    // Reset filters
    Livewire::test(AdminCustomerIndex::class)
        ->set('segmentFilter', (string) $segmentA->id)
        ->call('resetFilters')
        ->assertSet('segmentFilter', '')
        ->assertSee('Alpha Member')
        ->assertSee('Beta Member')
        ->assertSee('Unassigned Person');
});
