
<script language="javascript">
function UPDATE()
{
	var error = false;
	$("#submit").attr("disabled", true);
	$('#submit').html('<i class="fa fa-spinner fa-spin"></i>');
	var input = ["user","text"];
	
	$.each(input, function( index, value ) {
  		$('#'+ value).removeClass('is-invalid');
  		$('#span-'+ value).remove();
	});
	

	$.ajax({
		data: {
        	"_token": $("meta[name=csrf-token]").attr("content"),
			"product_id": $('#product_id').val(),
			"user": $('#user').val(),
			"title": $('#title').val(),
			"text": $('#text').val(),
			"date": $('#date').val(),
			"rating": $('#rating').val(),
			"channel_id": $('#channel_id').val(),
			"link": $('#link').val(),
        },
		type: 'PUT',
		url: '{{ route('route_toursdk_review.update',$review->id) }}'
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
                <div class="card-header">Edit Category</div>
                <div class="card-body">
				
<form onSubmit="UPDATE(); return false;">
<div id="result"></div>


<div class="form-group">
	<label for="product_id">Product :</label>
    <select class="form-control" id="product_id">
       @foreach($products as $product)
       	<option value="{{ $product->id }}" {{ ($product->id==$review->product_id) ? 'selected' : '' }}>{{ $product->name }}</option>
       @endforeach
	</select>
</div>

<div class="form-group">   
				 <label for="datetimepicker1">Date :</label>           
                <div class='input-group' id='datetimepicker1'>
                    <input type="text" id="date" name="date" value="{{ $review->date }}" class="form-control bg-white" readonly>
                    <div class="input-group-append input-group-addon text-muted">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                    
                </div>
 		<script type="text/javascript">
            $(function () {
                $('#date').datetimepicker({
					format: 'YYYY-MM-DD 00:00:00',
					showTodayButton: true,
					showClose: true,
					ignoreReadonly: true,
					icons: {
                    	time: "fa fa-clock"
                	},
					widgetPositioning: {
            			horizontal: 'left',
            			vertical: 'bottom'
        			}
				});
            });
        </script>    
</div>

<div class="form-group">
	<label for="channel_id">Channel :</label>
    <select class="form-control" id="channel_id">
       @foreach($channels as $channel)
       	<option value="{{ $channel->id }}" {{ ($channel->id==$review->channel_id) ? 'selected' : '' }}>{{ $channel->name }}</option>
       @endforeach
	</select>
</div>


<div class="form-group">
	<label for="rating">Rating :</label>
    <select class="form-control" id="rating">
      <option value="5" {{ ($review->rating=='5') ? 'selected' : '' }}>5</option>
      <option value="4" {{ ($review->rating=='4') ? 'selected' : '' }}>4</option>
      <option value="3" {{ ($review->rating=='3') ? 'selected' : '' }}>3</option>
      <option value="2" {{ ($review->rating=='2') ? 'selected' : '' }}>2</option>
      <option value="1" {{ ($review->rating=='1') ? 'selected' : '' }}>1</option>
	</select>
</div>

<div class="form-group">
	<label for="user">User :</label>
	<input type="text" id="user" name="user" class="form-control" placeholder="User" value="{{$review->user}}" autocomplete="off">
</div> 

<div class="form-group">
	<label for="title">Title :</label>
	<input type="text" id="title" name="title" class="form-control" value="{{$review->title}}" placeholder="Title">
</div>

<div class="form-group">
	<label for="text">Text :</label>
    <textarea class="form-control" id="text" name="text" rows="5" placeholder="Text">{{$review->text}}</textarea>
</div>

<div class="form-group">
	<label for="link">Link :</label>
	<input type="text" id="link" name="link" class="form-control" placeholder="Link" value="{{$review->link}}">
</div>
     
<button  class="btn btn-danger" type="button" onClick="$.fancybox.close();"><i class="fa fa-window-close"></i> Cancel</button>
<button id="submit" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
</form>
</div>
</div>       




				
        </div>
    </div>

</div>