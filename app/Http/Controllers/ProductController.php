<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(Product::with('category')->get());
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->with('category')->firstOrFail();
        return new ProductResource($product);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('image') ? $request->file('image')->store('products', 'public') : null;

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'image' => $imagePath,
        ]);

        return new ProductResource($product);
    }

    public function update(Request $request, $id)
{
    $this->authorizeAdmin();

    Log::info('Update function accessed with ID: ' . $id);

    // Fetch the product or fail with a 404 error if not found
    $product = Product::findOrFail($id);

    // Validate incoming request data
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'category_id' => 'required|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Handle image upload if a new image is provided
    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        // Store the new image
        $imagePath = $request->file('image')->store('products', 'public');
    } else {
        // Keep the existing image if no new image is provided
        $imagePath = $product->image;
    }

    // Update the product with the validated data
    $product->update([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'price' => $request->price,
        'stock' => $request->stock,
        'category_id' => $request->category_id,
        'image' => $imagePath,
    ]);

    // Return the updated product as a resource
    return new ProductResource($product);
}




public function destroy($id)
{
    $this->authorizeAdmin();

    $product = Product::findOrFail($id);

    // Delete image if exists
    if ($product->image) {
        Storage::disk('public')->delete($product->image);
    }

    $product->delete();

    return response()->json(['message' => 'Product deleted successfully']);
}


    private function authorizeAdmin()
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }
}
