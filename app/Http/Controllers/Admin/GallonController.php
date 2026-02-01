<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FirebaseRealtimeService;
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

        $db = app(FirebaseRealtimeService::class);
        $db->add('gallons', $data);

        return back()->with('success', 'Gallon added successfully');
    }

    public function gallonupdate(Request $request, $id)
    {
        $db = app(FirebaseRealtimeService::class);
        $gallon = $db->get('gallons', $id);
        if ($gallon === null) {
            abort(404);
        }

        $data = $request->validate([
            'size'        => 'required|string',
            'quantity'    => 'required|integer',
            'addon_price' => 'required|numeric',
            'image'       => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            if (!empty($gallon['image']) && file_exists(public_path($gallon['image']))) {
                @unlink(public_path($gallon['image']));
            }
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('gallons'), $filename);
            $data['image'] = 'gallons/' . $filename;
        } else {
            $data['image'] = $gallon['image'] ?? null;
        }

        $data['status'] = $data['quantity'] > 0 ? 'available' : 'out';

        $db->update('gallons', $id, $data);

        return back()->with('success', 'Gallon updated successfully');
    }

    public function gallondestroy($id)
    {
        $db = app(FirebaseRealtimeService::class);
        if ($db->get('gallons', $id) === null) {
            abort(404);
        }
        $db->delete('gallons', $id);
        return back()->with('success', 'Gallon deleted');
    }
}
