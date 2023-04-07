<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemFormRequest;
use App\Http\Resources\ItemResoruce;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::orderBy('id', 'desc');
        $items = $this->_per_page > 0 ? $items->paginate($this->_per_page) : $items->select('id', 'name')->get();

        if ($this->_per_page < 0) {
            return JsonResource::collection($items);
        }

        return ItemResoruce::collection($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemFormRequest $request)
    {
        $item = Item::create($request->validated());

        return new ItemResoruce($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return new ItemResoruce($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemFormRequest $request, Item $item)
    {
        $item->fill($request->validated());
        $item->save();

        return new ItemResoruce($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json(["message" => "Item has been deleted."]);
    }
}
