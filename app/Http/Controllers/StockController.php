<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StockResource;
use App\Http\Requests\StockFormRequest;

class StockController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = Stock::orderBy('id', 'desc');
        if (request()->has('item_id')) {
            $stocks->where('item_id', request()->item_id);
        }
        $stocks = $this->_per_page > 0 ? $stocks->paginate($this->_per_page) : $stocks->get();

        return StockResource::collection($stocks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockFormRequest $request)
    {
        DB::beginTransaction();

        try {
            $stock = Stock::create($request->validated());
                
            $item = Item::find($stock->item_id);
            $newQuantity = intval($stock->quantity) + intval($item->stock);
            
            DB::update("UPDATE items SET stock = $newQuantity WHERE id = $item->id");
            
            DB::commit();
            
            return new StockResource($stock);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Stock $stock)
    {
        return new StockResource($stock);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockFormRequest $request, Stock $stock)
    {
        DB::beginTransaction();

        try {
            // quantity = new quantity - previous
            // the above quantity will be added to the item stock
            $quantity = $request->quantity - $stock->quantity;

            $stock->fill($request->validated());
            $stock->save();
            
            $item = Item::find($stock->item_id);
            $newQuantity = intval($item->stock) + $quantity;
            
            DB::update("UPDATE items SET stock = $newQuantity WHERE id = $item->id");
            
            DB::commit();
            
            return new StockResource($stock);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Stock $stock)
    {
        DB::beginTransaction();

        try {
            $item = Item::find($stock->item_id);
           
            $newQuantity = intval($item->stock) - intval($stock->quantity);
            // dd("UPDATE items SET stock = $newQuantity WHERE id = $item->id");
            DB::update("UPDATE items SET stock = $newQuantity WHERE id = $item->id");
            
            $stock->delete();

            DB::commit();
            
            return response()->json(["message" => "Stock has been deleted."]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }
}
