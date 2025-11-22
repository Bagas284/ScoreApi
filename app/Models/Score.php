<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    protected $fillable = ['semester', 'mataKuliah', 'gambar', 'mine', 'email'];
    protected $hidden = ['created_at', 'updated_at'];
}
