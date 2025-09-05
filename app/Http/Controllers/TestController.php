<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return view('test');
    }

    public function create()
    {
        // show the upload form
    }

    public function store(Request $request)
{
    $request->validate([
        'image' => 'required|image|max:25600', // 25MB
    ]);

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('uploads', 'public');
        dd('✅ File uploaded successfully! Path: ' . $path);
    } else {
        dd('❌ Upload failed.');
    }
}

}
