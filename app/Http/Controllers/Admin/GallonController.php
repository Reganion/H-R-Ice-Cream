<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallon;
use Illuminate\Http\Request;

class GallonController extends Controller
{
    public function gallonstore(Request $request)
    {
        $data = $request->validate([
            'size' => 'required|string',
            'quantity' => 'required|integer',
            'addon_price' => 'required|numeric',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('gallons'), $filename);
            $data['image'] = 'gallons/' . $filename;
        }

        $data['status'] = $data['quantity'] > 0 ? 'available' : 'out';

        Gallon::create($data);

        return back()->with('success', 'Gallon added successfully');
    }

    public function gallonupdate(Request $request, $id)
    {
        $gallon = Gallon::findOrFail($id);

        $data = $request->validate([
            'size'        => 'required|string',
            'quantity'    => 'required|integer',
            'addon_price' => 'required|numeric',
            'image'       => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            if ($gallon->image && file_exists(public_path($gallon->image))) {
                @unlink(public_path($gallon->image));
            }
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('gallons'), $filename);
            $data['image'] = 'gallons/' . $filename;
        } else {
            $data['image'] = $gallon->image;
        }

        $data['status'] = $data['quantity'] > 0 ? 'available' : 'out';

        $gallon->update($data);

        return back()->with('success', 'Gallon updated successfully');
    }

    public function gallondestroy($id)
    {
        $gallon = Gallon::findOrFail($id);
        $gallon->delete();
        return back()->with('success', 'Gallon deleted');
    }
}
