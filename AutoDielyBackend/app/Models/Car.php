<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Car extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'registration_number', 'is_registered'];

    public function parts()
    {
        return $this->hasMany(Part::class);
    }

    public function scopeisRegistered(Builder $query): Builder
    {
        return $query->where('is_registered', '=', 1);
    }

    public function scopealphabetically(Builder $query): Builder
    {
        return $query->orderBy('name', 'asc');
    }
}
