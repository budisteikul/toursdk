<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Channel;

class ChannelHelper {

    public static function getDescription($shoppingcart)
    {
        $description = $shoppingcart->booking_channel;
        $channel = Channel::where('name',$shoppingcart->booking_channel)->first();
        if($channel) $description = $channel->description;
        if($description=="") $description = $channel->name;
        return $description;
    }
}
?>