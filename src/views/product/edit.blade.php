@inject('ImageHelper', budisteikul\toursdk\Helpers\ImageHelper)
<script language="javascript">
function UPDATE()
{
	var error = false;
	$("#submit").attr("disabled", true);
	$('#submit').html('<i class="fa fa-spinner fa-spin"></i>');
	var input = ["name","bokun_id"];
	
	$.each(input, function( index, value ) {
  		$('#'+ value).removeClass('is-invalid');
  		$('#span-'+ value).remove();
	});
	
	@foreach($product->images as $image)
	var image_{{ $image->id }} = $('#image_{{ $image->id }}').val();
	if($('#del_image_{{ $image->id }}').is(':checked'))
	{
		var del_image_{{ $image->id }} = $('#del_image_{{ $image->id }}').val();
	}
	@endforeach

	$.ajax({
		data: {
        	"_token": $("meta[name=csrf-token]").attr("content"),
			"name": $('#name').val(),
			"bokun_id": $('#bokun_id').val(),
			"category_id": $('#category_id').val(),
			"key": '{{$file_key}}',
			@foreach($product->images as $image)
				image_{{ $image->id }}: image_{{ $image->id }}, del_image_{{ $image->id }}: del_image_{{ $image->id }},
			@endforeach
        },
		type: 'PUT',
		url: '{{ route('route_toursdk_product.update',$product->id) }}'
		}).done(function( data ) {
			
			if(data.id=="1")
			{
       				$('#dataTableBuilder').DataTable().ajax.reload( null, false );
					$.fancybox.close();	
			}
			else
			{
				$.each( data, function( index, value ) {
					$('#'+ index).addClass('is-invalid');
						if(value!="")
						{
							$('#'+ index).after('<span id="span-'+ index  +'" class="invalid-feedback" role="alert"><strong>'+ value +'</strong></span>');
						}
					});
				$("#submit").attr("disabled", false);
				$('#submit').html('<i class="fa fa-save"></i> {{ __('Save') }}');
			}
		});
	
	
	return false;
}
</script>
<div class="h-100" style="width:99%">		

    <div class="row justify-content-center">
        <div class="col-md-12 pr-0 pl-0 pt-0 pb-0">
             <div class="card">
                <div class="card-header">Edit Product</div>
                <div class="card-body">
				
<form onSubmit="UPDATE(); return false;">
<div id="result"></div>


<div class="form-group">
	<label for="name">Name :</label>
	<input type="text" id="name" name="name" class="form-control" placeholder="Name" autocomplete="off" value="{{ $product->name }}">
</div>

<div class="form-group">
	<label for="bokun_id">Bokun ID :</label>
	<input type="text" id="bokun_id" name="bokun_id" class="form-control" placeholder="Bokun ID" autocomplete="off" value="{{ $product->bokun_id }}">
</div> 

  <div class="form-group">
    <label for="category_id">Category</label>
    <select class="form-control" id="category_id">
      <option value="0">No Category</option>
      @foreach($categories as $category)
      <option value="{{ $category->id }}" {{  ($category->id == $product->category_id) ? "selected" : "" }}>{{ $category->name }}</option>
      @endforeach
    </select>
  </div>


<div class="form-group">
	<div class="row">
		@foreach($product->images->sortBy('sort') as $image)
				<div class="col-auto" style="margin-top:10px;">
					<img style=" height:150px; " class="image-photo rounded" src="{{ $ImageHelper->urlImageCloudinary($image->public_id,'250','250') }}" >
					<div class="form-row align-items-center pt-1">
                    	<div class="col-auto">
							<input type="text" class="form-control text-center" style="width:50px;" id="image_{{ str_ireplace("-","_",$image->id) }}" name="image_{{ str_ireplace("-","_",$image->id) }}" value="{{ $image->sort }}">
						</div>
						<div class="col-auto">
							<div class="form-check form-check-inline">
								<input type="checkbox" class="form-check-input" id="del_image_{{ $image->id }}" name="del_image_{{ $image->id }}" value="hapus">
								<label class="form-check-label" for="del_image_{{ $image->id }}">
								Delete
								</label>
							</div>
						</div>
					</div>
				</div>
		@endforeach
	</div>
</div>

<div class="form-group">
<label>Image :</label>
<div id="status"></div>
<div id="mulitplefileuploader"><b class="fa fa-plus"> Upload Imge </b></div>
<script>
$(document).ready(function()
{
var settings = {
    url: "{{ route('route_coresdk_filetemp.index') }}",
    multiple:true,
	dragDrop:true,
	maxFileCount:-1,
    fileName: "myfile",
    allowedTypes:"jpg,jpeg,png",	
    returnType:"json",
	acceptFiles:"image/*",
	uploadStr:"<i class=\"fa fa-folder-open\"></i> Browse",
	onSuccess:function(files,data,xhr)
    {
		$.each( data, function( index, value ) {
		});	
    },
    showDelete:true,
	formData: { key: '{{ $file_key }}' , _token: $("meta[name=csrf-token]").attr("content") },
    deleteCallback: function(data,pd)
	{
		
    for(var i=0;i<data.length;i++)
    {
						
						$.ajax({
							beforeSend: function(request) {
    							request.setRequestHeader("X-CSRF-TOKEN", $("meta[name=csrf-token]").attr("content"));
  						},
     						type: 'DELETE',
     						url: '{{ route('route_coresdk_filetemp.index') }}/'+ data[i]
						}).done(function( msg ) {
							
						});	
     }      
    pd.statusbar.hide();
	}
}
var uploadObj = $("#mulitplefileuploader").uploadFile(settings);
});
</script>
</div>
     
<button  class="btn btn-danger" type="button" onClick="$.fancybox.close();"><i class="fa fa-window-close"></i> Cancel</button>
<button id="submit" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
</form>
</div>
</div>       




				
        </div>
    </div>

</div>