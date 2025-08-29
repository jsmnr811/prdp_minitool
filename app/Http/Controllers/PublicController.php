<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PublicController extends Controller
{

    public function auctions(){
        $products = Product::with('auction', 'bids')->withCount('bids')->get();
        return view('public.auctions', compact('products'));
    }

}
