<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteContent extends Model
{
    protected $table = 'website_contents';
    
    protected $fillable = ['section', 'key', 'value'];
}