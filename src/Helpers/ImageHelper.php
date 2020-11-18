<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ImageHelper {

    public static function uploadImageCloudinary($file)
    {
        \Cloudinary::config(array( 
            "cloud_name" => env('CLOUDINARY_NAME'), 
            "api_key" => env('CLOUDINARY_KEY'), 
            "api_secret" => env('CLOUDINARY_SECRET') 
        ));
        $response = \Cloudinary\Uploader::upload(storage_path('app').'/'. $file, Array('unique_filename'=>false,'use_filename'=>true,'folder' => env('CLOUDINARY_NAME') .'/images'));
        Storage::disk('local')->delete($file);
        return $response;
    }

    public static function deleteImageCloudinary($public_id)
    {
        \Cloudinary::config(array( 
            "cloud_name" => env('CLOUDINARY_NAME'), 
            "api_key" => env('CLOUDINARY_KEY'), 
            "api_secret" => env('CLOUDINARY_SECRET') 
        ));
        \Cloudinary\Uploader::destroy($public_id);
    }

}
?>
