<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EquipmentCategoryController extends Controller
{
    public function index(): View
    {
        $categories = EquipmentCategory::withCount('equipments')
            ->orderBy('name')
            ->paginate(15);

        return view('equipment.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:equipment_categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = EquipmentCategory::create($validated);

        ActivityLogger::log('CREATE', 'Perlengkapan', "Menambahkan kategori perlengkapan '{$category->name}'");

        return back()->with('toast_success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, EquipmentCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('equipment_categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($validated);

        ActivityLogger::log('UPDATE', 'Perlengkapan', "Memperbarui kategori perlengkapan '{$category->name}'");

        return back()->with('toast_success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroy(EquipmentCategory $category): RedirectResponse
    {
        $itemCount = $category->equipments()->count();

        if ($itemCount > 0) {
            return back()->with('toast_error', "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh {$itemCount} barang.");
        }

        $name = $category->name;
        $category->delete();

        ActivityLogger::log('DELETE', 'Perlengkapan', "Menghapus kategori perlengkapan '{$name}'");

        return back()->with('toast_success', "Kategori '{$name}' berhasil dihapus.");
    }
}
