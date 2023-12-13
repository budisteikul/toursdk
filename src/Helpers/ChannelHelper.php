<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Channel;

class ChannelHelper {

    public static function getDescription($name)
    {
        $channel = Channel::where('name',$name)->first();
        $description = $channel->description;
        if($description=="") $description = $channel->name;
        return $description;
    }
}
?>