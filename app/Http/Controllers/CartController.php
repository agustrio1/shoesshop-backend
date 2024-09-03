<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

        $carts = Cart::with('product', 'user')->get();
        return response()->json($carts);
    }

    public function show($id)
    {
        $cart = Cart::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->with('product')
                    ->firstOrFail();

        return response()->json($cart);
    }

    public function getByUserId($userId)
    {
        $this->authorizeAdmin();

        $carts = Cart::with('product')->where('user_id', $userId)->get();
        return response()->json($carts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Ambil produk berdasarkan product_id dari request dengan kunci eksklusif untuk menghindari race condition
            $product = Product::lockForUpdate()->findOrFail($request->product_id);

            if ($product->stock < $request->quantity) {
                return response()->json(['error' => 'Stok tidak mencukupi'], 400);
            }

            // Hitung total harga berdasarkan harga produk dan kuantitas
            $totalPrice = $request->quantity * $product->price;

            // Kurangi stok produk
            $product->decrement('stock', $request->quantity);

            // Buat cart baru
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $totalPrice,
            ]);

            return response()->json($cart, 201);
        });
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $id) {
            $cart = Cart::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

            $product = Product::lockForUpdate()->findOrFail($cart->product_id);

            // Cek apakah stok mencukupi untuk perubahan jumlah
            $quantityDifference = $request->quantity - $cart->quantity;
            if ($product->stock < $quantityDifference) {
                return response()->json(['error' => 'Stok tidak mencukupi'], 400);
            }

            // Kurangi atau tambahkan stok sesuai perubahan
            $product->decrement('stock', $quantityDifference);

            // Update cart
            $cart->update([
                'quantity' => $request->quantity,
                'price' => $request->quantity * $product->price,
            ]);

            return response()->json($cart);
        });
    }

    public function destroy($id)
    {
        $cart = Cart::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->firstOrFail();

        $product = Product::findOrFail($cart->product_id);

        // Kembalikan stok produk
        // $product->increment('stock', $cart->quantity);

        $cart->delete();

        return response()->json(['message' => 'Cart deleted successfully']);
    }

    private function authorizeAdmin()
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
