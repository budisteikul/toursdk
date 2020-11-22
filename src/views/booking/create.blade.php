
<script language="javascript">
function CREATE_BOOKING()
{
  window.location.href = '{{route('route_toursdk_booking.index')}}/calendar?activityId='+ $("#bokun_id").val();
  return false;
}
</script>
 
<div class="h-100" style="width:99%">		
 
    <div class="row justify-content-center">
        <div class="col-md-12 pr-0 pl-0 pt-0 pb-0">
             <div class="card">
             
	<div class="card-header">Create Booking</div>
	<div class="card-body">
				
<form onSubmit="CREATE_BOOKING(); return false;">

<div id="result"></div>

 

<div class="form-group">
    <label for="bokun_id">Product</label>
    <select class="form-control" id="bokun_id">
      @foreach($products as $product)
      <option value="{{ $product->bokun_id }}">{{ $product->name }}</option>
      @endforeach
    </select>
</div>


	<button  class="btn btn-danger" type="button" onClick="$.fancybox.close();"><i class="fa fa-window-close"></i> Cancel</button>
	<button id="submit" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create</button>
	</form>
	</div>
</div>       
		
        
        		
        </div>
    </div>

</div>