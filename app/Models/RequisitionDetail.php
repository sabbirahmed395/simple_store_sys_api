<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        // "requisition_id",
        "item_id",
        "quantity"
    ];

    protected $appends = [
        "item_name"
    ];

    public function getItemNameAttribute()
    {
        return $this->item->name;
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function requisition()
    {
        return $this->belongsTo(RequisitionMaster::class, 'requisition_id', 'id');
    }

    public function issue()
    {
        return $this->hasMany(RequisitionIssue::class);
    }
}
