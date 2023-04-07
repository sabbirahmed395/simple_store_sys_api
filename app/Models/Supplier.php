<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "phone",
        "email",
        "address",
        "status"
    ];

    public static $status = [
        "active",
        "inactive"
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
