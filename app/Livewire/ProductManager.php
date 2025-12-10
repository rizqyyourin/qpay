<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductManager extends Component
{
    use WithPagination, WithFileUploads;

    public bool $showForm = false;
    public ?Product $editingProduct = null;
    public ?Product $productToDelete = null;
    public ?int $userId = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('string|max:1000')]
    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public float $price = 0;

    #[Validate('required|numeric|min:0')]
    public int $stock = 0;

    #[Validate('string|max:255')]
    public string $barcode = '';

    #[Validate('nullable|image|mimes:jpeg,png,gif,webp|max:5120')]
    public $image = null;

    public ?string $currentImage = null;

    public string $search = '';

    public function mount(): void
    {
        $this->userId = Auth::id();
    }

    public function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'image.max' => 'Ukuran gambar terlalu besar. Maksimal 5MB.',
            'image.mimes' => 'Format gambar hanya menerima: JPG, PNG, GIF, WebP.',
            'image.image' => 'File harus berupa gambar.',
        ];
    }

    public function openForm(?int $productId = null): void
    {
        if ($productId) {
            $product = Product::findOrFail($productId);
            $this->editingProduct = $product;
            $this->name = $product->name ?? '';
            $this->description = $product->description ?? '';
            $this->price = $product->price ?? 0;
            $this->stock = $product->stock ?? 0;
            $this->barcode = $product->barcode ?? '';
            $this->currentImage = $product->image;
            $this->image = null;
        } else {
            $this->editingProduct = null;
            $this->name = '';
            $this->description = '';
            $this->price = 0;
            $this->stock = 0;
            $this->barcode = '';
            $this->currentImage = null;
            $this->image = null;
        }
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function resetForm(): void
    {
        $this->editingProduct = null;
        $this->name = '';
        $this->description = '';
        $this->price = 0;
        $this->stock = 0;
        $this->barcode = '';
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        // Handle image upload
        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
        }

        if ($this->editingProduct) {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'stock' => $this->stock,
                'barcode' => $this->barcode,
            ];
            
            if ($imagePath) {
                // Delete old image if exists
                if ($this->editingProduct->image) {
                    Storage::disk('public')->delete($this->editingProduct->image);
                }
                $data['image'] = $imagePath;
            }
            
            $this->editingProduct->update($data);
            session()->flash('message', 'Product updated successfully!');
            $this->closeForm();
        } else {
            $product = Product::create([
                'user_id' => $this->userId,
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'stock' => $this->stock,
                'barcode' => $this->barcode,
                'image' => $imagePath,
            ]);
            
            $this->closeForm();
            session()->flash('message', 'Product created successfully!');
        }
    }

    public function delete(Product $product): void
    {
        $product->delete();
        session()->flash('message', 'Product deleted successfully!');
    }

    public function confirmDelete(Product $product): void
    {
        $this->productToDelete = $product;
    }

    public function cancelDelete(): void
    {
        $this->productToDelete = null;
    }

    public function proceedDelete(): void
    {
        if ($this->productToDelete) {
            $this->delete($this->productToDelete);
            $this->productToDelete = null;
        }
    }

    public function render()
    {
        return view('livewire.product-manager', [
            'products' => Product::where('user_id', $this->userId)
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->paginate(10),
        ]);
    }
}
