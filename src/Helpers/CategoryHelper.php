<?php
namespace budisteikul\toursdk\Helpers;
use budisteikul\toursdk\Models\Category;

class CategoryHelper {
	
    

    public static function structure($id)
    {
        print("<ul>");
        $categories = Category::where('parent_id',$id)->get();
        foreach($categories as $category)
        {
             print('<li class="parent_li">');
             print('<span>'.$category->name.'</span>');
             if(@count($category->ChildCategories))
             {
                self::structure($category->id);
             }
             print("</li>");
        }
        print("</ul>");
    }

    public static function getParent($id)
    {
        $status = true;
        $array = array();
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
             if(@count($category->ChildCategories))
             {
             	
                $a = self::getChild_($category->id,$a);
                $array = array_merge($array,$a);
             }
             
        }
        return $array;
	}

}
?>
