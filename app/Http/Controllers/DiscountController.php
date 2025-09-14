<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Models\Discount;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $data = Discount::all();
        return response()->json($data);
    }

    public function store(StoreDiscountRequest $request)
    {
        $request->validated();

        if ($request->active) {
            Discount::where('active', true)->update(['active' => false]);
        }

        $discount = Discount::create($request->only('discount_percent', 'active'));

        return response()->json($discount, 201);
    }

    public function update(UpdateDiscountRequest $request,$id)
    {
        $discount = Discount::findOrFail($id);

        $request->validated();

        if ($request->active) {
            Discount::where('active', true)->update(['active' => false]);
        }

        $discount->update($request->only('discount_percent', 'active'));

        return response()->json($discount);
    }

    public function destroy($id)
    {
        Discount::findOrFail($id)->delete();
        return response()->json(['message' => 'Xóa giảm giá thành công']);
    }

     public function getActive()
    {
        $discount = Discount::where('active', true)->select('discount_percent')->first();
        return response()->json($discount);
    }
}
