<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'required|string|max:50',
            'email'        => 'required|email|max:255',
            'license_no'   => 'required|string|max:100',
            'license_type' => 'required|string|max:100',
            'image'        => 'nullable|image|max:2048',
        ], [
            'name.required'       => 'Driver name is required.',
            'phone.required'      => 'Phone number is required.',
            'email.required'      => 'Email address is required.',
            'email.email'         => 'Please enter a valid email address.',
            'license_no.required' => 'License number is required.',
            'license_type.required'=> 'License type is required.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $dir = public_path('img/drivers');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $image = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $image->getClientOriginalName());
            $image->move($dir, $filename);
            $imagePath = 'img/drivers/' . $filename;
        }

        $lastDriver = Driver::orderBy('created_at', 'desc')->first();
        $nextNum = $lastDriver && $lastDriver->driver_code
            ? (int) preg_replace('/\D/', '', $lastDriver->driver_code) + 1
            : 1;
        $driverCode = 'DRV' . str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

        Driver::create([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'email'        => $request->email,
            'license_no'   => $request->license_no,
            'license_type' => $request->license_type,
            'image'        => $imagePath,
            'status'       => Driver::STATUS_AVAILABLE,
            'driver_code'  => $driverCode,
        ]);

        return back()->with('success', 'Driver added successfully.');
    }
}
