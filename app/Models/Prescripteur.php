<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prescripteur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nom', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
