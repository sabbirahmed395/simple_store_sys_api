<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionMaster extends Model
{
    use HasFactory;

    public $table = 'requisitions';

    protected $fillable = [
        "requisition_for",
        "notes",
        "status"
    ];

    protected $appends = [
        'item_count',
        'item_quantity'
    ];
    
    protected static function boot()
    {
        parent::boot();

        // static::updating(function ($model) {
        //     if (in_array($model->status, ['approved', 'issued'])) {
        //         throw new \Exception('Only pending requisition can be updated.');
        //     }
        // });

        // static::deleting(function ($model) {
        //     if (in_array($model->status, ['approved', 'issued'])) {
        //         throw new \Exception('Only pending requisition can be updated.');
        //     }
        // });
    }

    public function getItemCountAttribute()
    {
        return $this->details()->count();
    }

    public function getItemQuantityAttribute()
    {
        return $this->details->sum('quantity');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requisition_for', 'id');
    }

    public function details()
    {
        return $this->hasMany(RequisitionDetail::class, 'requisition_id', 'id');
    }

    public function issue()
    {
        return $this->hasManyThrough(RequisitionIssue::class, RequisitionDetail::class, 'requisition_id', 'requisition_detail_id', 'id', 'id');
    }
}
