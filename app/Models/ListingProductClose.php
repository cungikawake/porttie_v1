<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use DB;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use App\Traits\Commentable;
use App\Traits\HashId;


class ListingProductClose extends Model
{
    use \Spiritix\LadaCache\Database\LadaCacheTrait;
    
    protected $table = 'listing_product_close';
    
    protected $fillable = [
          'listing_id', 'close_date', 'close_name'
    ];

    protected $casts = [
        'close_date' => 'date'
    ];
}
