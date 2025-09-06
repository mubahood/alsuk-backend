<?php

namespace App\Models;

use Dflydev\DotAccessData\Util;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        //created
        self::created(function ($m) {});

        //cerating
        self::creating(function ($m) {
            $pro_with_same_vid = Product::where('local_id', $m->local_id)->first();
            if ($pro_with_same_vid != null) {
                throw new \Exception("Product with same local_id already exists", 1);
            }
        });

        //updating
        self::updating(function ($m) {

            return $m;
        });
        //updated
        self::updated(function ($m) {
            $m->sync(Utils::get_stripe());
        });

        self::deleting(function ($m) {
            try {
                $imgs = Image::where('parent_id', $m->id)->orwhere('product_id', $m->id)->get();
                foreach ($imgs as $img) {
                    $img->delete();
                }
            } catch (\Throwable $th) {
                //throw $th;
            }
        });
    }

    //getter for feature_photo
    public function getFeaturePhotoAttribute($value)
    {

        //check if value contains images/
        if (str_contains($value, 'images/')) {
            return $value;
        }
        $value = 'images/' . $value;
        return $value;
    }



    public function update_stripe_price($new_price)
    {

        return; 
    }

    public function sync($stripe)
    {

        return;
    }
    public function getRatesAttribute()
    {
        $imgs = Image::where('parent_id', $this->id)->orwhere('product_id', $this->id)->get();
        return json_encode($imgs);
    }


    protected $appends = ['category_text'];
    public function getCategoryTextAttribute()
    {
        $d = ProductCategory::find($this->category);
        if ($d == null) {
            return 'Not Category.';
        }
        return $d->category;
    }

    //getter for colors from json
    public function getColorsAttribute($value)
    {
        $resp = str_replace('\"', '"', $value);
        $resp = str_replace('[', '', $resp);
        $resp = str_replace(']', '', $resp);
        $resp = str_replace('"', '', $resp);
        return $resp;
    }

    //setter for colors to json
    public function setColorsAttribute($value)
    {
        if ($value != null) {
            if (strlen($value) > 2) {
                $value = json_encode($value);
                $this->attributes['colors'] = $value;
            }
        }
    }

    //sett keywords to json
    public function setKeywordsAttribute($value)
    {
        if ($value != null) {
            if (strlen($value) > 2) {
                $value = json_encode($value);
                $this->attributes['keywords'] = $value;
            }
        }
    }

    //getter for keywords from json
    public function getKeywordsAttribute($value)
    {
        if ($value == null) {
            return [];
        }

        try {
            $resp = json_decode($value);
            return $resp;
        } catch (\Throwable $th) {
            return [];
        }

        return $resp;
    }


    //getter for sizes
    public function getSizesAttribute($value)
    {
        $resp = str_replace('\"', '"', $value);
        $resp = str_replace('[', '', $resp);
        $resp = str_replace(']', '', $resp);
        $resp = str_replace('"', '', $resp);
        return $resp;
    }

    //setter for sizes
    public function setSizesAttribute($value)
    {
        if ($value != null) {
            if (strlen($value) > 2) {
                $value = json_encode($value);
                $this->attributes['sizes'] = $value;
            }
        }
    }

    //has many Image
    public function images()
    {
        return $this->hasMany(Image::class, 'product_id', 'id');
    }
    
    //belongs to Administrator (user)
    public function owner()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user', 'id');
    }


    protected $casts = [
        'summary' => 'json',
    ];
}
