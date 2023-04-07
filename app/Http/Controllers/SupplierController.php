<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierFormRequest;
use App\Http\Resources\SupplierResoruce;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc');
        $suppliers = $this->_per_page > 0 ? $suppliers->paginate($this->_per_page) : $suppliers->select('id', 'name')->get();

        if ($this->_per_page < 0) {
            return JsonResource::collection($suppliers);
        }

        return SupplierResoruce::collection($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierFormRequest $request)
    {
        $supplier = Supplier::create($request->validated());

        return new JsonResource($supplier);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return new JsonResource($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierFormRequest $request, Supplier $supplier)
    {
        $supplier->fill($request->validated());
        $supplier->save();
        
        return new JsonResource($supplier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json(["message" => "Supplier has been deleted."]);
    }
}
