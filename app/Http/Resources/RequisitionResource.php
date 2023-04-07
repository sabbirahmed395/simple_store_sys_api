<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequisitionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $responseArray = [
            'id' => $this->id,
            'date' => $this->created_at->format('d F Y h:i A'),
            'requisition_for' => $this->user->name,
            'item_count' => $this->item_count,
            'item_quantity' => $this->item_quantity,
            'status' => $this->status,
        ];

        return $responseArray;
    }
}
