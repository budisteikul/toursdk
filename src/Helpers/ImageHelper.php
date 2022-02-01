<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Product;
use budisteikul\toursdk\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ImageHelper {

    public static function env_cloudinaryName()
    {
        return env("CLOUDINARY_NAME");
    }

    public static function env_cloudinaryKey()
    {
        return env("CLOUDINARY_KEY");
    }

    public static function env_cloudinarySecret()
    {
        return env("CLOUDINARY_SECRET");
    }

    public static function uploadQrcodeCloudinary($url)
    {
        \Cloudinary::config(array( 
            "cloud_name" => self::env_cloudinaryName(), 
            "api_key" => self::env_cloudinaryKey(), 
            "api_secret" => self::env_cloudinarySecret() 
        ));
        $response = \Cloudinary\Uploader::upload($url, Array('unique_filename'=>true,'use_filename'=>false,'folder' => env('APP_NAME') .'/qr-code'));
        return $response;
    }

    public static function uploadImageCloudinary($file)
    {
        \Cloudinary::config(array( 
            "cloud_name" => self::env_cloudinaryName(), 
            "api_key" => self::env_cloudinaryKey(), 
            "api_secret" => self::env_cloudinarySecret() 
        ));
        $response = \Cloudinary\Uploader::upload(storage_path('app').'/'. $file, Array('unique_filename'=>false,'use_filename'=>true,'folder' => env('APP_NAME') .'/images'));
        Storage::disk('local')->delete($file);
        return $response;
    }

    public static function deleteImageCloudinary($public_id)
    {
        \Cloudinary::config(array( 
            "cloud_name" => self::env_cloudinaryName(), 
            "api_key" => self::env_cloudinaryKey(), 
            "api_secret" => self::env_cloudinarySecret() 
        ));
        \Cloudinary\Uploader::destroy($public_id);
    }
	
	public static function urlImageCloudinary($public_id,$width=0,$height=0)
	{
		\Cloudinary::config(array( 
			"cloud_name" => self::env_cloudinaryName(), 
			"api_key" => self::env_cloudinaryKey(), 
			"api_secret" => self::env_cloudinarySecret() 
		));

		if($width>0 && $height>0)
		{
			$url = cloudinary_url($public_id, array("width" => $width, "height" => $height, "crop" => "fill","secure"=>true));
		}
		else
		{
			$url = cloudinary_url($public_id, array("secure"=>true));
		}
		return $url;
		
	}

    public static function cover(Product $product)
    {
        $url = '';
        $image = $product->images->sortBy('sort')->first();
        if(isset($image)) $url = self::urlImageCloudinary($image->public_id,300,150);
        return $url;
    }

    public static function thumbnail(Product $product)
    {
        $url = '';
        $image = $product->images->sortBy('sort')->first();
        if(isset($image)) $url = self::urlImageCloudinary($image->public_id,80,80);
        return $url;
    }

}
?>
