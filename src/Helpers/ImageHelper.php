<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as ImageIntervention;
use Ramsey\Uuid\Uuid;
use File;

class ImageHelper {

    public static function env_googleCloudStorageBucket()
    {
        return env('GOOGLE_CLOUD_STORAGE_BUCKET');
    }

    public static function urlImageGoogle($public_id,$width=0,$height=0)
    {
        //$url = 'https://storage.googleapis.com/'. self::env_googleCloudStorageBucket() .'/images/original/'. $public_id;
        $url = 'https://'. self::env_googleCloudStorageBucket() .'/images/original/'. $public_id;
        $url = str_ireplace("original","w_".$width."-h_".$height."",$url);
        return $url;
        
    }

    public static function uploadImageGoogle($file)
    {
        $image_id = Uuid::uuid4()->toString() .'.jpg';

        $img = ImageIntervention::make(storage_path('app').'/'. $file);
        Storage::disk('gcs')->put( 'images/original/'. $image_id, $img->encode('jpg', 75)); 

        $img = ImageIntervention::make(storage_path('app').'/'. $file);
        $img->resize(600, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->crop(600, 400);
        Storage::disk('gcs')->put( 'images/w_600-h_400/'. $image_id, $img->encode('jpg', 75));

        
        $img = ImageIntervention::make(storage_path('app').'/'. $file);
        $img->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->crop(300, 150);
        Storage::disk('gcs')->put('images/w_300-h_150/'. $image_id, $img->encode('jpg', 75));

        $img = ImageIntervention::make(storage_path('app').'/'. $file);
        $img->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->crop(300, 200);
        Storage::disk('gcs')->put('images/w_300-h_200/'. $image_id, $img->encode('jpg', 75));

        $img = ImageIntervention::make(storage_path('app').'/'. $file);
        $img->fit(250, 250);
        Storage::disk('gcs')->put('images/w_250-h_250/'. $image_id, $img->encode('jpg', 75)); 

        $img = ImageIntervention::make(storage_path('app').'/'. $file);
        $img->fit(80, 80);
        Storage::disk('gcs')->put('images/w_80-h_80/'. $image_id, $img->encode('jpg', 75));
        
        $response['public_id'] = $image_id;
        $response['secure_url'] = 'https://'. self::env_googleCloudStorageBucket() .'/images/original/'. $image_id;

        return $response;
    }

    public static function deleteImageGoogle($public_id)
    {
        Storage::disk('gcs')->delete('images/original/'. $public_id);
        Storage::disk('gcs')->delete('images/w_600-h_400/'. $public_id);
        Storage::disk('gcs')->delete('images/w_300-h_150/'. $public_id);
        Storage::disk('gcs')->delete('images/w_300-h_200/'. $public_id);
        Storage::disk('gcs')->delete('images/w_250-h_250/'. $public_id);
        Storage::disk('gcs')->delete('images/w_80-h_80/'. $public_id);
    }

    public static function cover(Product $product)
    {
        $url = '';
        $image = $product->images->sortBy('sort')->first();
        if(isset($image)) $url = self::urlImageGoogle($image->public_id,300,150);
        return $url;
    }

    public static function thumbnail(Product $product)
    {
        $url = '';
        $image = $product->images->sortBy('sort')->first();
        if(isset($image)) $url = self::urlImageGoogle($image->public_id,80,80);
        return $url;
    }

}
?>
