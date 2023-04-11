<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use Illuminate\Http\Request;
use App\Models\RequisitionMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ItemResoruce;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ItemFormRequest;
use App\Http\Resources\RequisitionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RequisitionController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->hasAnyRole(['admin', 'store executive'])) {
            throw new UnauthorizedException(403, "You are not allowed.");
        }

        if (auth()->user()->hasRole('admin')) {
            $requisitions = RequisitionMaster::whereIn('status', ['pending', 'approved'])->orderBy('id', 'desc');
        }

        if (auth()->user()->hasRole('store executive')) {
            $requisitions = RequisitionMaster::whereIn('status', ['approved', 'issued'])->orderBy('id', 'desc');
        }
        
        $requisitions = $this->_per_page > 0 ? $requisitions->paginate($this->_per_page) : $requisitions->get();

        return RequisitionResource::collection($requisitions);
    }

    public function myRequisition()
    {
        $requisitions = RequisitionMaster::where('requisition_for', auth()->user()->id)->orderBy('id', 'desc');

        $requisitions = $this->_per_page > 0 ? $requisitions->paginate($this->_per_page) : $requisitions->get();
     
        return RequisitionResource::collection($requisitions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $requisition = RequisitionMaster::create([
                'requisition_for' => auth()->user()->id,
                'notes' => $request->notes,
                'status' => 'pending'
            ]);

            foreach ($request['details'] as $detail) {
                $requisition->details()->create([
                    'item_id' => $detail['item_id'],
                    'quantity' => $detail['quantity']
                ]);
            }

            DB::commit();
    
            return new RequisitionResource($requisition);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RequisitionMaster $requisition)
    {
        $requisition->load('user');
        return new JsonResource($requisition);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequisitionMaster $requisition)
    {
        if ($requisition->status != 'pending') {
            throw new \Exception("Update operation can not be possible.");
        }

        if ($requisition->requisition_for != Auth::user()->id) {
            throw new UnauthorizedException(403, "You can not update others requisition.");
        }

        DB::beginTransaction();

        try {
            $requisition->notes = $request->notes;
            $requisition->save();

            DB::delete('DELETE FROM requisition_details WHERE requisition_id = ?',[$requisition->id]);

            foreach ($request['details'] as $detail) {
                $requisition->details()->create([
                    'item_id' => $detail['item_id'],
                    'quantity' => $detail['quantity']
                ]);
            }

            DB::commit();
    
            return new RequisitionResource($requisition);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequisitionMaster $requisition)
    {
        if ($requisition->status != 'pending') {
            throw new \Exception("Delete operation can not be possible.");
        }

        DB::beginTransaction();

        try {
            DB::delete('DELETE FROM requisition_details WHERE requisition_id = ?',[$requisition->id]);

            $requisition->delete();

            DB::commit();
    
            return response()->json(["message" => "Requisition has been deleted."]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    public function approve(RequisitionMaster $requisition)
    {
        if (!Auth::user()->hasRole('admin')) {
            throw new UnauthorizedException(403, "Only admin can approve requisition");
        }

        if ($requisition->status != 'pending') {
            throw new \Exception("Only pending requisition can be approved");
        }

        $requisition->status = 'approved';
        $requisition->save();

        return new RequisitionResource($requisition);
    }

    public function issue(RequisitionMaster $requisition)
    {
        if (!Auth::user()->hasRole('store executive')) {
            throw new UnauthorizedException(403, "Only store executive can issue requisition");
        }

        if ($requisition->status != 'approved') {
            throw new \Exception("Only approved requisition can be issued");
        }

        DB::beginTransaction();

        try {
            foreach ($requisition->details as $detail) {
                $itemId = $detail->item_id;
                $remaining = $quantity = $detail->quantity;

                // check totol stock on particular item
                $availableStock = Stock::where('item_id', $itemId)->sum('quantity');
                $issuedStock = Stock::where('item_id', $itemId)->sum('issued');
                $availableStock = $availableStock - $issuedStock;

                $itemName = $detail->item->toArray()['name'];
                if ($availableStock < $quantity) {
                    throw new \Exception("Issue operation can not be possible due to insufficient stock on `$itemName`");
                }
                
                // dd('stock pass');

                $stocks = Stock::select(['id', 'quantity', 'issued'])->where('item_id', $itemId)->orderBy('id', 'asc')->get()->toArray();

                foreach ($stocks as $stock) {
                    if ($stock['quantity'] - $stock['issued'] == 0) { // no available stock to be issued
                        continue;
                    }

                    if ($remaining <= 0) break;

                    $quantityToBeIssued = $remaining;
                    Log::debug($quantityToBeIssued);

                    // $remaininig = 150 - 120 = 30 -> next iteration | 
                    // $remaininig = 120 - 120 = 0 -> break on next iteration |
                    // $remaininig = 110 - 120 = -10 -> break on next iteration |
                    $remaining = $remaining - ($stock['quantity'] - $stock['issued']);
                    Log::debug("Remaining: " . $remaining);

                    if ($remaining >= 0) {
                        $quantityToBeIssued = $stock['quantity'] - $stock['issued'];
                    }

                    if ($remaining < 0) {
                        $quantityToBeIssued = ($stock['quantity'] - $stock['issued']) + $remaining;
                    }

                    // create issue row
                    $detail->issue()->create([
                        'stock_id' => $stock['id'],
                        'quantity' => $quantityToBeIssued
                    ]);
                    
                    // update issued column on stock row
                    $stockRow = Stock::find($stock['id']);
                    Log::debug("Issued "  . $stockRow->issued);
                    Log::debug("quantity to be issued "  . $quantityToBeIssued);
                    $stockRow->issued = $stockRow->issued + $quantityToBeIssued;
                    Log::debug("After set "  . $stockRow->issued);
                    $stockRow->save();

                    unset($stockRow);
                }

                $item = Item::find($itemId);
                $item->stock = $item->stock - $quantity;
                $item->save();
            }

            // issuing items
            $requisition->status = 'issued';
            $requisition->save();

            DB::commit();

            return new RequisitionResource($requisition);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    public function issuedDetails(RequisitionMaster $requisition) {
        if (!Auth::user()->hasRole('store executive')) {
            throw new UnauthorizedException(403, "Only store executive can issue requisition");
        }

        if ($requisition->status != 'issued') {
            throw new \Exception("Only issued requisition can be displayed");
        }
        
        $requisition = RequisitionMaster::with(['user', 'details', 'issue', 'issue.stock', 'issue.stock.item'])->find($requisition->id);
        // return $requisition->details;
        // $requisition->load();

        return new JsonResource($requisition);
    }
}
