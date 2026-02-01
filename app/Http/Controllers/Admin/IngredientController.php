<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseRealtimeService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class IngredientController extends Controller
{
    /** Same image location as store: public/ingredients/, stored path ingredients/filename */
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

        $db = app(FirebaseRealtimeService::class);
        $db->add('ingredients', [
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
        $db = app(FirebaseRealtimeService::class);
        $ingredient = $db->get('ingredients', $id);
        if ($ingredient === null) {
            abort(404);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:Ingredients,Flavor',
            'quantity' => 'required|numeric|min:0',
            'unit'     => 'required',
            'image'    => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->saveIngredientImage(
            $request->file('image'),
            $ingredient['image'] ?? null
        );

        $db->update('ingredients', $id, [
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
        $db = app(FirebaseRealtimeService::class);
        if ($db->get('ingredients', $id) === null) {
            abort(404);
        }
        $db->delete('ingredients', $id);
        return back()->with('success', 'Ingredient deleted');
    }
}
