<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Category;

class CategoryHelper {
	


    public static function nameCategory($id,$separator)
    {
    	$strlen = strlen($separator) + 1;
    	$id = Category::find($id);
    	if(isset($id))
    	{
    		$array_cat = self::getParent($id->id);
			$categories = Category::findMany($array_cat)->sortBy(function($model) use ($array_cat) {
				return array_search($model->getKey(), array_reverse($array_cat));
			});

			$string = "";
			foreach($categories as $category )
			{
				$string .= " ".$separator." ". $category->name;
			}
			return substr($string,$strlen);
    	}
    	else
    	{
    		return "Doesn't have category";
    	}
        	
		
    }

    public static function structure($id)
    {
        
        $categories = Category::where('parent_id',$id)->get();
        foreach($categories as $category)
        {
             print("<ul>");
             print('<li class="parent_li">');
             print('<span>'.$category->name.'</span>');
             if(@count($category->child))
             {
                self::structure($category->id);
             }
             print("</li>");
             print("</ul>");
        }
        
    }

    public static function getParent($id)
    {
        $status = true;
        $array = array();

        if($id==0) return $array;

        while($status)
        {
            $category = Category::where('id',$id)->first();
            array_push($array,$category->id);
            if($category->parent_id>0)
            {
                $id = $category->parent_id;
            }
            else
            {
                $status = false;
            }
        }
        return $array;
    }

    public static function getChild($id)
    {
    	$array = array();
    	array_push($array,$id);
    	$array = self::getChild_($id,$array);
    	return $array;
    }

	public static function getChild_($id,$array)
	{
		$categories = Category::where('parent_id',$id)->get();
		foreach($categories as $category)
        {
             array_push($array,$category->id);
             $a = array();
             if(@count($category->child))
             {
             	
                $a = self::getChild_($category->id,$a);
                $array = array_merge($array,$a);
             }
             
        }
        return $array;
	}

}
?>
