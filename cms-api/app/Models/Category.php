<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Category.php

use App\Models\Article;

class Category extends Model
{
    protected $fillable = ['name'];

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
}
