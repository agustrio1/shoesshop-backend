<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shipping;

class ShippingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
       $shippings = Shipping::all();
        return response()->json($shippings);
    }

    public function show($id)
    {
        $shipping = Shipping::find($id);

        if (!$shipping) {
            return response()->json(['message' => 'Shipping not found.'], 404);
        }

        return response()->json($shipping);
    }

    public function store(Request $request)
    {
        // Check if the user is an admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'city' => 'required|string|max:255',
            'fee' => 'required|numeric',
        ]);

        $shipping = Shipping::create([
            'city' => $request->city,
            'fee' => $request->fee,
            'user_id' => Auth::id(),
        ]);

        return response()->json($shipping, 201);
    }

    public function update(Request $request, $id)
    {
        $shipping = Shipping::find($id);    

        if (!$shipping) {
            return response()->json(['message' => 'Shipping not found.'], 404);
        }

        // Check if the user is an admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shipping->update($request->all());
        return response()->json($shipping);
    }

    public function destroy($id)
    {
        $shipping = Shipping::find($id);

        if (!$shipping) {
            return response()->json(['message' => 'Shipping not found.'], 404);
        }

        // Check if the user is an admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shipping->delete();

        return response()->json(['message' => 'Shipping deleted.']);
    }
}
