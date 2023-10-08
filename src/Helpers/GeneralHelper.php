<?php
namespace budisteikul\toursdk\Helpers;

class GeneralHelper {

    

    public static function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function digitFormat($number,$digit)
    {
        $number = str_pad($number, $digit, '0', STR_PAD_LEFT);
        return $number;
    }

    public static function dateFormat($date="",$type="")
    {
        if($date=="") $date = \Carbon\Carbon::now()->toDateTimeString();
        
        
        switch($type)
        {
            case 1:
                return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
            break;
            case 2:
                return \Carbon\Carbon::parse($date)->format('d-m-Y H:i');
            break;
            case 3:
                return \Carbon\Carbon::parse($date)->format('l, d F Y, H:i');
            break;
            case 4:
                return \Carbon\Carbon::parse($date)->format('d F Y');
            break;
            case 5:
                return \Carbon\Carbon::parse($date)->format('d/m/Y');
            break;
            case 6:
                return \Carbon\Carbon::parse($date)->format('l, d F Y');
            break;
            case 7:
                return \Carbon\Carbon::parse($date)->format('Y-m-d 00:00:00');
            break;
            case 8:
                return \Carbon\Carbon::parse($date)->format('Y-m-d 23:59:59');
            break;
            case 9:
                return \Carbon\Carbon::parse($date)->format('d F Y, H:i');
            break;
            case 10:
                return \Carbon\Carbon::parse($date)->format('d F Y, H:i:s');
            break;
            case 11:
                return \Carbon\Carbon::parse($date)->format("D d.M'y");
            break;
            case 12:
                $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date, 'Asia/Jakarta');
                $new_date = $date->setTimezone('UTC')->format('Y-m-d\TH:i:s.v\Z');
                return $new_date;
            break;
            default:
                return \Carbon\Carbon::now()->toDateTimeString();
        }
    }

    public static function numberFormat($exp,$currency="")
    {
        if($currency=="") $currency = env('BOKUN_CURRENCY');

        if($currency=="IDR")
        {
            return number_format($exp, 0, ',',',');
        }
        else
        {
            $exp = number_format($exp, 2, '.','');
            $arr_val = explode('.',$exp);
            if(strlen((string)$arr_val[0])>=4)
            {
                $exp = round($exp);
                $exp = number_format((float)$exp, 2, '.', '');
                $exp = number_format($exp, 0, ',',',');
            }
            
            return $exp;
        }
        
    }

    public static function splitSpace($string,$number=4,$first=0)
    {
        $front = substr($string,0,$first);
        $string = substr($string,$first);
        $value = "";
        $max_string = strlen($string);
        $mod = $max_string % $number;
        $j = 0;
        for($i=0;$i<$max_string;$i++)
        {
            $value .= substr($string, $j, $number) .' ';
            $j += $number;
        }
        return trim($front .' '. $value);
    }

    public static function formatRupiah($angka)
    {
        $hasil_rupiah = "Rp " . number_format($angka,0,',','.');
        return $hasil_rupiah;
    }

    public static function roundCurrency($value,$currency="IDR")
    {
        if($currency=="IDR")
        {
            $hundred = substr($value, -3);
            if($hundred<500)
            {
                $value = $value - $hundred;
            }
            else
            {
                $value = $value + (1000-$hundred);
            }
        }
        return $value;
    }

}
?>