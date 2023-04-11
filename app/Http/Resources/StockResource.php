<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'item_name' => $this->item->name,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'issued' => $this->issued,
        ];
    }
}
