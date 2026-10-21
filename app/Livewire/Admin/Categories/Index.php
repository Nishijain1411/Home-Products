<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $search = '';

    public string $statusFilter = '';

    public string $parentFilter = '';

    public string $typeFilter = '';

    public array $selected = [];

    public bool $selectAll = false;

    public bool $showBulkDeleteModal = false;

    public $showModal = false;

    public $showDeleteModal = false;

    public $showImportModal = false;

    public $importFile = null;

    public array $importErrors = [];

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public $editingId = null;

    public $deleteId = null;

    public $name = '';

    public $slug = '';

    public $description = '';

    public $collection_type = 'manual';

    public $is_active = true;

    public $image = null;

    public $existingImage = null;

    public $sort_order = 0;

    public $seo_title = '';

    public $seo_description = '';

    public $parent_id = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'parentFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function buildFilteredCategoriesQuery()
    {
        return Category::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->when($this->parentFilter === 'root', function ($query) {
                $query->whereNull('parent_id');
            })
            ->when($this->parentFilter === 'sub', function ($query) {
                $query->whereNotNull('parent_id');
            })
            ->when($this->typeFilter !== '', function ($query) {
                $query->where('collection_type', $this->typeFilter);
            });
    }

    public function getCurrentPageIdsProperty(): array
    {
        return $this->buildFilteredCategoriesQuery()
            ->latest()
            ->paginate(15)
            ->map(fn ($cat) => (string) $cat->id)
            ->all();
    }

    public function updatedSelectAll($value): void
    {
        $pageIds = $this->currentPageIds;

        if ($value) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
        } else {
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        }
    }

    public function updatedSelected(): void
    {
        $this->syncSelectAllState();
    }

    public function syncSelectAllState(): void
    {
        $pageIds = $this->currentPageIds;
        if (empty($pageIds)) {
            $this->selectAll = false;

            return;
        }

        $diff = array_diff($pageIds, $this->selected);
        $this->selectAll = empty($diff);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedPage(): void
    {
        $this->syncSelectAllState();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedParentFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'parentFilter', 'typeFilter']);
        $this->resetPage();
        $this->syncSelectAllState();
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return ! empty($this->search)
            || $this->statusFilter !== ''
            || $this->parentFilter !== ''
            || $this->typeFilter !== '';
    }

    public function updatedName($value)
    {
        if (! $this->editingId && empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $category = Category::findOrFail($id);

        $this->editingId = $category->id;

        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description;
        $this->collection_type = $category->collection_type;
        $this->image = null;
        $this->existingImage = $category->image;
        $this->sort_order = $category->sort_order ?? 0;
        $this->is_active = $category->is_active;
        $this->seo_title = $category->seo_title ?? '';
        $this->seo_description = $category->seo_description ?? '';
        $this->parent_id = $category->parent_id;
        $this->resetValidation();

        $this->showModal = true;
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('categories', 'slug')
                    ->ignore($this->editingId),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'collection_type' => [
                'required',
                Rule::in(['manual', 'automated']),
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'boolean',
            ],

            'seo_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seo_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
        ];
    }

    public function removeImage(): void
    {
        $this->image = null;
        $this->resetValidation('image');
    }

    public function removeExistingImage(): void
    {
        if ($this->editingId && $this->existingImage) {
            $category = Category::find($this->editingId);
            if ($category && $category->image) {
                if (Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                $category->update(['image' => null]);
            }
        }

        $this->existingImage = null;
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->image) {
            $validated['image'] = $this->image->store(
                'categories',
                'public'
            );
        } elseif ($this->editingId) {
            $validated['image'] = $this->existingImage;
        } else {
            $validated['image'] = null;
        }

        if ($this->editingId) {

            $category = Category::findOrFail($this->editingId);

            // Remove old image if a new image was uploaded
            if (
                $this->image &&
                $category->image &&
                $category->image !== $validated['image'] &&
                Storage::disk('public')->exists($category->image)
            ) {
                Storage::disk('public')->delete(
                    $category->image
                );
            }

            $category->update($validated);

            session()->flash(
                'success',
                'Category updated successfully.'
            );

        } else {

            Category::create($validated);

            session()->flash(
                'success',
                'Category created successfully.'
            );
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $category = Category::findOrFail($this->deleteId);

        if ($category->products()->exists()) {
            $this->errorMessage = 'This category cannot be deleted because products are assigned to it.';
            session()->flash('error', $this->errorMessage);

            $this->closeDeleteModal();

            return;
        }

        if ($category->children()->exists()) {
            $this->errorMessage = 'This category cannot be deleted because it contains subcategories.';
            session()->flash('error', $this->errorMessage);

            $this->closeDeleteModal();

            return;
        }

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        session()->flash(
            'success',
            'Category deleted successfully.'
        );

        $this->closeDeleteModal();
    }

    protected function authorizeBulkAction(): void
    {
        if (! auth()->check()) {
            abort(401, 'Unauthenticated.');
        }

        $user = auth()->user();

        if (! $user->canAccessAdmin()) {
            abort(403, 'Unauthorized. Access restricted to store administrators.');
        }

        if (method_exists($user, 'hasPermissionTo') && $user->roles()->exists()) {
            if (! $user->hasAnyRole(['Super Administrator', 'Store Administrator', 'Catalogue Manager']) && ! $user->can('categories')) {
                abort(403, 'Unauthorized. You do not have permission to manage categories.');
            }
        }
    }

    protected function getValidatedSelectedIds(): array
    {
        $this->authorizeBulkAction();

        if (empty($this->selected)) {
            $this->errorMessage = 'No categories were selected for this bulk action.';
            session()->flash('error', $this->errorMessage);

            return [];
        }

        $ids = array_values(array_filter(array_map('intval', $this->selected), fn ($id) => $id > 0));

        if (empty($ids)) {
            $this->errorMessage = 'The selected category IDs are invalid.';
            session()->flash('error', $this->errorMessage);
            $this->clearSelection();

            return [];
        }

        return $ids;
    }

    public function bulkActivate(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        $count = Category::whereIn('id', $ids)->update(['is_active' => true]);

        $message = "{$count} ".Str::plural('category', $count).' activated successfully.';
        $this->errorMessage = null;
        $this->successMessage = $message;
        session()->flash('success', $message);

        $this->clearSelection();
    }

    public function bulkDeactivate(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        $count = Category::whereIn('id', $ids)->update(['is_active' => false]);

        $message = "{$count} ".Str::plural('category', $count).' deactivated successfully.';
        $this->errorMessage = null;
        $this->successMessage = $message;
        session()->flash('success', $message);

        $this->clearSelection();
    }

    public function confirmBulkDelete(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function closeBulkDeleteModal(): void
    {
        $this->showBulkDeleteModal = false;
    }

    public function bulkDelete(): void
    {
        $ids = $this->getValidatedSelectedIds();
        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            $categories = Category::withCount(['products', 'children'])->whereIn('id', $ids)->get();

            $deletableCategories = [];
            $protectedCategories = [];

            foreach ($categories as $category) {
                $issues = [];
                if ($category->products_count > 0) {
                    $issues[] = "{$category->products_count} assigned ".Str::plural('product', $category->products_count);
                }
                if ($category->children_count > 0) {
                    $issues[] = "{$category->children_count} sub".Str::plural('category', $category->children_count);
                }

                if (! empty($issues)) {
                    $protectedCategories[] = "{$category->name} (".implode(', ', $issues).')';
                } else {
                    $deletableCategories[] = $category;
                }
            }

            if (empty($deletableCategories)) {
                $reasons = implode('; ', $protectedCategories);
                $this->errorMessage = "Cannot delete selected categories because of existing dependencies: {$reasons}. Please reassign or delete dependent items first.";
                session()->flash('error', $this->errorMessage);

                return;
            }

            $deletedCount = 0;
            foreach ($deletableCategories as $category) {
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }

                $category->delete();
                $deletedCount++;
            }

            if (! empty($protectedCategories)) {
                $skippedReasons = implode('; ', $protectedCategories);
                $message = "{$deletedCount} ".Str::plural('category', $deletedCount)." deleted successfully. Skipped: {$skippedReasons}.";
            } else {
                $message = "{$deletedCount} ".Str::plural('category', $deletedCount).' deleted successfully.';
            }

            $this->errorMessage = null;
            $this->successMessage = $message;
            session()->flash('success', $message);
        });

        $this->closeBulkDeleteModal();
        $this->clearSelection();
    }

    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);

        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        session()->flash(
            'success',
            'Category status updated.'
        );
    }

    public function closeModal()
    {
        $this->showModal = false;

        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function resetForm()
    {
        $this->reset([
            'editingId',
            'name',
            'slug',
            'description',
            'image',
            'existingImage',
            'parent_id',
            'seo_title',
            'seo_description',
        ]);

        $this->collection_type = 'manual';
        $this->sort_order = 0;
        $this->is_active = true;

        $this->resetValidation();
    }

    public function openImportModal(): void
    {
        $this->importFile = null;
        $this->importErrors = [];
        $this->resetValidation('importFile');
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->importErrors = [];
        $this->resetValidation('importFile');
    }

    public function dismissAlert(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function export(): StreamedResponse
    {
        $categories = $this->buildFilteredCategoriesQuery()
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $filename = 'categories-export-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($categories) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Name',
                'Slug',
                'Parent Category',
                'Collection Type',
                'Is Active',
                'Sort Order',
                'Description',
                'SEO Title',
                'SEO Description',
            ]);

            foreach ($categories as $category) {
                fputcsv($handle, [
                    $category->name,
                    $category->slug,
                    $category->parent?->name ?? '',
                    $category->collection_type ?? 'manual',
                    $category->is_active ? '1' : '0',
                    $category->sort_order ?? 0,
                    $category->description ?? '',
                    $category->seo_title ?? '',
                    $category->seo_description ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSample(): StreamedResponse
    {
        $filename = 'categories-sample-template.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Name',
                'Slug',
                'Parent Category',
                'Collection Type',
                'Is Active',
                'Sort Order',
                'Description',
                'SEO Title',
                'SEO Description',
            ]);

            fputcsv($handle, [
                'Electronics',
                'electronics',
                '',
                'manual',
                '1',
                '0',
                'All kinds of electronics and personal tech gadgets.',
                'Electronics & Gadgets',
                'Shop latest electronics at best prices.',
            ]);

            fputcsv($handle, [
                'Smartphones',
                'smartphones',
                'Electronics',
                'manual',
                '1',
                '1',
                'iOS and Android mobile phones and accessories.',
                'Smartphones & Mobile Phones',
                'Explore the newest 5G smartphones.',
            ]);

            fputcsv($handle, [
                'Laptops & Computers',
                'laptops-computers',
                'Electronics',
                'manual',
                '1',
                '2',
                'High performance laptops, desktops and workstations.',
                'Laptops & Computers',
                'Best laptops for work, study and gaming.',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $path = $this->importFile->getRealPath();
        if (! $path || ! file_exists($path)) {
            $this->addError('importFile', 'Unable to read the uploaded CSV file.');

            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->addError('importFile', 'Could not open the uploaded CSV file.');

            return;
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headerRow = fgetcsv($handle);
        if (! $headerRow || empty(array_filter($headerRow))) {
            fclose($handle);
            $this->addError('importFile', 'The CSV file is empty or missing headers.');

            return;
        }

        $colMap = [];
        foreach ($headerRow as $index => $colName) {
            $normalized = strtolower(trim((string) $colName));
            $normalized = str_replace([' ', '-', '_'], '', $normalized);
            $colMap[$normalized] = $index;
        }

        $nameIdx = $colMap['name'] ?? $colMap['categoryname'] ?? null;

        if ($nameIdx === null) {
            fclose($handle);
            $this->addError('importFile', 'Missing required CSV header: "Name" must be present.');

            return;
        }

        $slugIdx = $colMap['slug'] ?? null;
        $parentIdx = $colMap['parentcategory'] ?? $colMap['parent'] ?? $colMap['parentname'] ?? $colMap['parentslug'] ?? null;
        $descIdx = $colMap['description'] ?? $colMap['desc'] ?? null;
        $collectionTypeIdx = $colMap['collectiontype'] ?? $colMap['collection'] ?? $colMap['type'] ?? null;
        $activeIdx = $colMap['isactive'] ?? $colMap['active'] ?? $colMap['status'] ?? null;
        $sortOrderIdx = $colMap['sortorder'] ?? $colMap['sort'] ?? $colMap['order'] ?? null;
        $seoTitleIdx = $colMap['seotitle'] ?? null;
        $seoDescIdx = $colMap['seodescription'] ?? $colMap['seodesc'] ?? null;

        $rows = [];
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue;
            }

            $name = trim((string) ($row[$nameIdx] ?? ''));

            if ($name === '') {
                $errors[] = "Row {$rowNumber}: Category Name is required.";
            }

            $rawSort = $sortOrderIdx !== null ? trim((string) ($row[$sortOrderIdx] ?? '')) : '';
            if ($rawSort !== '' && (! is_numeric($rawSort) || (int) $rawSort < 0)) {
                $errors[] = "Row {$rowNumber}: Sort Order must be a valid non-negative integer if provided.";
            }

            $rawCollectionType = $collectionTypeIdx !== null ? strtolower(trim((string) ($row[$collectionTypeIdx] ?? ''))) : 'manual';
            if ($rawCollectionType !== '' && ! in_array($rawCollectionType, ['manual', 'automated'], true)) {
                $errors[] = "Row {$rowNumber}: Collection Type must be either 'manual' or 'automated'.";
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'name' => $name,
                'slug' => $slugIdx !== null ? trim((string) ($row[$slugIdx] ?? '')) : '',
                'parent' => $parentIdx !== null ? trim((string) ($row[$parentIdx] ?? '')) : '',
                'collection_type' => in_array($rawCollectionType, ['manual', 'automated'], true) ? $rawCollectionType : 'manual',
                'is_active' => $activeIdx !== null ? trim((string) ($row[$activeIdx] ?? '')) : '1',
                'sort_order' => $rawSort !== '' && is_numeric($rawSort) ? (int) $rawSort : 0,
                'description' => $descIdx !== null ? trim((string) ($row[$descIdx] ?? '')) : '',
                'seo_title' => $seoTitleIdx !== null ? trim((string) ($row[$seoTitleIdx] ?? '')) : '',
                'seo_description' => $seoDescIdx !== null ? trim((string) ($row[$seoDescIdx] ?? '')) : '',
            ];
        }

        fclose($handle);

        if (empty($rows) && empty($errors)) {
            $this->addError('importFile', 'The uploaded CSV file contains no category data rows.');

            return;
        }

        if (! empty($errors)) {
            $this->importErrors = $errors;

            return;
        }

        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use (&$rows, &$createdCount, &$updatedCount) {
            $categoryMap = [];

            // Pass 1: Upsert all categories without parent_id (or keep existing parent_id)
            foreach ($rows as $index => $rowData) {
                $baseSlug = $rowData['slug'] !== '' ? Str::slug($rowData['slug']) : Str::slug($rowData['name']);
                if ($baseSlug === '') {
                    $baseSlug = 'category-'.strtolower(Str::random(6));
                }

                $existingCategory = null;
                if ($rowData['slug'] !== '') {
                    $existingCategory = Category::where('slug', $baseSlug)->first();
                }
                if (! $existingCategory) {
                    $existingCategory = Category::where('name', $rowData['name'])->first();
                }

                $slug = $baseSlug;
                $slugConflict = Category::where('slug', $slug)
                    ->when($existingCategory, fn ($q) => $q->where('id', '!=', $existingCategory->id))
                    ->exists();

                if ($slugConflict) {
                    $slug = $baseSlug.'-'.strtolower(Str::random(5));
                }

                $isActive = in_array(strtolower((string) $rowData['is_active']), ['1', 'true', 'yes', 'active'], true);

                $payload = [
                    'name' => $rowData['name'],
                    'slug' => $slug,
                    'collection_type' => $rowData['collection_type'],
                    'is_active' => $isActive,
                    'sort_order' => $rowData['sort_order'],
                    'description' => $rowData['description'] !== '' ? $rowData['description'] : null,
                    'seo_title' => $rowData['seo_title'] !== '' ? $rowData['seo_title'] : null,
                    'seo_description' => $rowData['seo_description'] !== '' ? $rowData['seo_description'] : null,
                ];

                if ($existingCategory) {
                    $existingCategory->update($payload);
                    $category = $existingCategory;
                    $updatedCount++;
                } else {
                    $category = Category::create($payload);
                    $createdCount++;
                }

                $categoryMap[strtolower($category->name)] = $category;
                $categoryMap[strtolower($category->slug)] = $category;
                $rows[$index]['model'] = $category;
            }

            // Pass 2: Link parent categories
            foreach ($rows as $rowData) {
                $parentIdentifier = trim($rowData['parent']);
                if ($parentIdentifier === '') {
                    continue;
                }

                $parentCategory = $categoryMap[strtolower($parentIdentifier)]
                    ?? Category::where('slug', Str::slug($parentIdentifier))->first()
                    ?? Category::where('name', $parentIdentifier)->first();

                if ($parentCategory && isset($rowData['model']) && $parentCategory->id !== $rowData['model']->id) {
                    $rowData['model']->update([
                        'parent_id' => $parentCategory->id,
                    ]);
                }
            }
        });

        $msg = 'Successfully imported '.($createdCount + $updatedCount)." categories ({$createdCount} created, {$updatedCount} updated).";

        $this->successMessage = $msg;
        session()->flash('success', $msg);

        $this->closeImportModal();
    }

    public function render()
    {
        $this->syncSelectAllState();

        $categories = $this->buildFilteredCategoriesQuery()
            ->with('parent')
            ->latest()
            ->paginate(15);

        $parentCategories = Category::query()
            ->where('is_active', true)
            ->when($this->editingId, function ($query) {
                $query->where('id', '!=', $this->editingId);
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.categories.index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }
}
