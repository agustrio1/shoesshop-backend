<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $addresses = $user->addresses;

        if ($addresses->isEmpty()) {
            return response()->json(['message' => 'No addresses found.'], 404);
        }

        return response()->json($addresses);
    }

    public function show($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found.'], 404);
        }

        return response()->json($address);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
        ]);

        $address = Address::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'user_id' => Auth::id(),
        ]);

        return response()->json($address, 201);
    }

    public function update(Request $request, $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found.'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
        ]);

        $address->update($request->all());

        return response()->json($address);
    }

    public function destroy($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found.'], 404);
        }

        $address->delete();

        return response()->json(['message' => 'Address deleted successfully.']);
    }

    public function getByUserId($userId)
    {
        if (Auth::user()->id != $userId && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $addresses = Address::where('user_id', $userId)->get();

        if ($addresses->isEmpty()) {
            return response()->json(['message' => 'No addresses found for this user.'], 404);
        }

        return response()->json($addresses);
    }
}
