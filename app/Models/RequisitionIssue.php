<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        // "requisition_id",
        "stock_id",
        "quantity"
    ];

    public $table = "requisition_issue";

    public $timestamps = false;

    public function detail()
    {
        return $this->belongsTo(RequisitionDetail::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
