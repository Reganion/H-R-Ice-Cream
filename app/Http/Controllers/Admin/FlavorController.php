<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class FlavorController extends Controller
{
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
            'name'         => 'required|string|max:255',
            'flavor_type'  => 'required|string|max:255',
            'category'     => 'required|in:Plain Flavor,Special Flavor,1 Topping,2 Toppings',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|max:2048',
            'mobile_image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->saveFlavorImage($request->file('image'));
        $mobileImagePath = $this->saveFlavorImage($request->file('mobile_image'));

        Flavor::create([
            'name'         => $request->name,
            'flavor_type'  => $request->flavor_type,
            'category'     => $request->category,
            'price'        => $request->price,
            'image'        => $imagePath,
            'mobile_image' => $mobileImagePath,
            'status'       => $request->price > 0 ? 'available' : 'out',
        ]);

        return back()->with('success', 'Flavor added successfully');
    }

    public function flavorupdate(Request $request, $id)
    {
        $flavor = Flavor::findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:255',
            'flavor_type'  => 'required|string|max:255',
            'category'     => 'required',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|max:2048',
            'mobile_image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $this->saveFlavorImage(
            $request->file('image'),
            $flavor->image
        );
        $mobileImagePath = $this->saveFlavorImage(
            $request->file('mobile_image'),
            $flavor->mobile_image
        );

        $flavor->update([
            'name'         => $request->name,
            'flavor_type'  => $request->flavor_type,
            'category'     => $request->category,
            'price'        => $request->price,
            'image'        => $imagePath,
            'mobile_image' => $mobileImagePath,
            'status'       => $request->price > 0 ? 'available' : 'out',
        ]);

        return back()->with('success', 'Flavor updated successfully');
    }

    public function flavordestroy($id)
    {
        $flavor = Flavor::findOrFail($id);
        $flavor->delete();
        return back()->with('success', 'Flavor deleted successfully');
    }
}
