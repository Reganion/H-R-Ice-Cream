<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseRealtimeService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class FlavorController extends Controller
{
    /** Same image location as store: public/flavors/, stored path flavors/filename */
    private function saveFlavorImage(?UploadedFile $file, ?string $existingPath = null): ?string
    {
        if ($file === null || !$file->isValid()) {
            return $existingPath;
        }
        $dir = public_path('flavors');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move($dir, $filename);
        return 'flavors/' . $filename;
    }

    public function flavorstore(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'flavor_type' => 'required|string|max:255',
            'category'    => 'required|in:Plain Flavor,Special Flavor,1 Topping,2 Toppings',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->saveFlavorImage($request->file('image'));

        $db = app(FirebaseRealtimeService::class);
        $db->add('flavors', [
            'name'        => $request->name,
            'flavor_type' => $request->flavor_type,
            'category'    => $request->category,
            'price'       => $request->price,
            'image'       => $imagePath,
            'status'      => $request->price > 0 ? 'available' : 'out',
        ]);

        return back()->with('success', 'Flavor added successfully');
    }

    public function flavorupdate(Request $request, $id)
    {
        $db = app(FirebaseRealtimeService::class);
        $flavor = $db->get('flavors', $id);
        if ($flavor === null) {
            abort(404);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'flavor_type' => 'required|string|max:255',
            'category'    => 'required',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->saveFlavorImage(
            $request->file('image'),
            $flavor['image'] ?? null
        );

        $db->update('flavors', $id, [
            'name'        => $request->name,
            'flavor_type' => $request->flavor_type,
            'category'    => $request->category,
            'price'       => $request->price,
            'image'       => $imagePath,
            'status'      => $request->price > 0 ? 'available' : 'out',
        ]);

        return back()->with('success', 'Flavor updated successfully');
    }

    public function flavordestroy($id)
    {
        $db = app(FirebaseRealtimeService::class);
        if ($db->get('flavors', $id) === null) {
            abort(404);
        }
        $db->delete('flavors', $id);
        return back()->with('success', 'Flavor deleted successfully');
    }
}
