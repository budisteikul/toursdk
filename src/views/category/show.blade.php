@inject('Category', budisteikul\toursdk\Helpers\CategoryHelper)
<div style="width:60%">   

    <div class="row justify-content-center">
        <div class="col-md-12 pr-0 pl-0 pt-0 pb-0">
             <div class="card">
                <div class="card-header">Structure Category</div>
                <div class="card-body">
        


<div class="tree">
<ul>
@foreach($root_categories as $root_category)
  <li class="parent_li">
    <span>{{ $root_category->name }}</span>
    @if(count($root_category->ChildCategories))
      {{ $Category->structure($root_category->id) }}
    @endif
  </li>
@endforeach
</ul>     
</div>



</div>
</div>       




        
        </div>

    </div>

</div>