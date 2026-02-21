<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class IngredientController extends Controller
{
    private function saveIngredientImage(?UploadedFile $file, ?string $existingPath = null): ?string
    {
        if ($file === null || !$file->isValid()) {
            return $existingPath;
        }
        $dir = public_path('ingredients');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move($dir, $filename);
        return 'ingredients/' . $filename;
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:Ingredients,Flavor',
            'quantity' => 'required|numeric|min:0',
            'unit'     => 'required',
            'image'    => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->saveIngredientImage($request->file('image'));

        Ingredient::create([
            'name'     => $request->name,
            'type'     => $request->type,
            'quantity' => $request->quantity,
            'unit'     => $request->unit,
            'image'    => $imagePath,
            'status'   => $request->quantity > 0 ? 'available' : 'out',
        ]);

        return back()->with('success', 'Ingredient added successfully');
    }

    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:Ingredients,Flavor',
            'quantity' => 'required|numeric|min:0',
            'unit'     => 'required',
            'image'    => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->saveIngredientImage(
            $request->file('image'),
            $ingredient->image
        );

        $ingredient->update([
            'name'     => $request->name,
            'type'     => $request->type,
            'quantity' => $request->quantity,
            'unit'     => $request->unit,
            'image'    => $imagePath,
            'status'   => $request->quantity > 0 ? 'available' : 'out',
        ]);

        return back()->with('success', 'Ingredient updated successfully');
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();
        return back()->with('success', 'Ingredient deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ingredient_ids' => ['required', 'array', 'min:1'],
            'ingredient_ids.*' => ['integer', 'exists:ingredients,id'],
        ]);

        $ids = collect($validated['ingredient_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $deletedCount = Ingredient::query()
            ->whereIn('id', $ids)
            ->delete();

        return back()->with('success', $deletedCount . ' selected item(s) deleted successfully');
    }
}
