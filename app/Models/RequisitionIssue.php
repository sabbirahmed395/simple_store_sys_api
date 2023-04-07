<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionIssue extends Model
{
    use HasFactory;

    public $table = "requisition_issue";

    public $timestamps = false;

    public function detail()
    {
        $this->belongsTo(RequisitionDetail::class);
    }
}
