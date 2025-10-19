<?php

namespace App\Services;

use App\Http\Requests\Api\CreateCartRequest;
use App\Http\Requests\Api\UpdateCartRequest;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function create(CreateCartRequest $request): Cart
    {
        $validatedData = $request->validated();

        $product = Product::findOrFail($validatedData['product_id']);
        $cart = Cart::firstOrNew(['user_id' => auth()->id(), 'product_id' => $product->id]);
        $newQuantity = $cart->quantity + $validatedData['quantity'];

        throw_if(
            $newQuantity > $product->stock,
            ValidationException::withMessages([
                'quantity' => 'The quantity must not be greater than stock.',
            ])
        );

        $cart->quantity = $newQuantity;
        $cart->save();

        return $cart;
    }

    public function update(UpdateCartRequest $request, Cart $cart): Cart
    {
        throw_if($cart->user_id !== auth()->id(), ModelNotFoundException::class);

        $newQuantity = $request->validated('quantity');

        throw_if(
            $newQuantity > $cart->product->stock,
            ValidationException::withMessages([
                'quantity' => 'The quantity must not be greater than stock.',
            ])
        );

        $cart->update(['quantity' => $newQuantity]);

        return $cart;
    }
}
