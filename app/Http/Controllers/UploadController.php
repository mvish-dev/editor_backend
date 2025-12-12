<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            // Store in 'public/uploads'
            $path = $file->storeAs('uploads', $filename, 'public');

            // Generate full URL
            $url = asset('storage/' . $path);

            return response()->json(['url' => $url], 200);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
