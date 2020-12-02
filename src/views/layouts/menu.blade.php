<!-- ##################################################################### -->
      <hr class="sidebar-divider my-0">
      <li class="nav-item 
      
        {{ (request()->is('cms/toursdk/product*')) ? 'active' : '' }}
        {{ (request()->is('cms/toursdk/category*')) ? 'active' : '' }}
        {{ (request()->is('cms/toursdk/channel*')) ? 'active' : '' }}
      
      ">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
          <i class="fas fa-tag"></i>
          <span>LIBRARY</span>
        </a>
        <div id="collapse1" class="collapse 
        
        {{ (request()->is('cms/toursdk/product*')) ? 'show' : '' }}
        {{ (request()->is('cms/toursdk/category*')) ? 'show' : '' }}
        {{ (request()->is('cms/toursdk/channel*')) ? 'show' : '' }}
        
        " aria-labelledby="heading1" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            
            <a class="collapse-item {{ (request()->is('cms/toursdk/product*')) ? 'active' : '' }}" href="{{ route('route_toursdk_product.index') }}"><i class="far fa-circle"></i> {{ __('Product') }}</a>
            
            <a class="collapse-item {{ (request()->is('cms/toursdk/category*')) ? 'active' : '' }}" href="{{ route('route_toursdk_category.index') }}"><i class="far fa-circle"></i> {{ __('Category') }}</a>
            
            <a class="collapse-item {{ (request()->is('cms/toursdk/channel*')) ? 'active' : '' }}" href="{{ route('route_toursdk_channel.index') }}"><i class="far fa-circle"></i> {{ __('Channel') }}</a>
            
           
          </div>
        </div>
      </li>
      <!-- ##################################################################### -->
      <hr class="sidebar-divider my-0">
      <li class="nav-item 
      
        {{ (request()->is('cms/toursdk/booking*')) ? 'active' : '' }}
      
      ">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
          <i class="fas fa-shopping-cart"></i>
          <span>ORDER</span>
        </a>
        <div id="collapse2" class="collapse 
        
        {{ (request()->is('cms/toursdk/booking*')) ? 'show' : '' }}
        
        " aria-labelledby="heading1" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            
            <a class="collapse-item {{ (request()->is('cms/toursdk/product*')) ? 'active' : '' }}" href="{{ route('route_toursdk_booking.index') }}"><i class="far fa-circle"></i> {{ __('Booking') }}</a>
            
           
          </div>
        </div>
      </li>
      <!-- ##################################################################### -->
      <hr class="sidebar-divider my-0">
      <li class="nav-item 
      
        {{ (request()->is('cms/toursdk/review*')) ? 'active' : '' }}
      
      ">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
          <i class="fas fa-comment"></i>
          <span>FEEDBACK</span>
        </a>
        <div id="collapse3" class="collapse 
        
        {{ (request()->is('cms/toursdk/review*')) ? 'show' : '' }}
        
        " aria-labelledby="heading1" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            
            <a class="collapse-item {{ (request()->is('cms/toursdk/review*')) ? 'active' : '' }}" href="{{ route('route_toursdk_review.index') }}"><i class="far fa-circle"></i> {{ __('Review') }}</a>
            
           
          </div>
        </div>
      </li>

 <!-- ##################################################################### -->

 <hr class="sidebar-divider my-0">
      <li class="nav-item 
      
        {{ (request()->is('cms/toursdk/page*')) ? 'active' : '' }}
      
      ">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse4" aria-expanded="true" aria-controls="collapse4">
          <i class="fas fa-globe-asia"></i>
          <span>WEBSITE</span>
        </a>
        <div id="collapse4" class="collapse 
        
        {{ (request()->is('cms/toursdk/page*')) ? 'show' : '' }}
        
        " aria-labelledby="heading1" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            
            <a class="collapse-item {{ (request()->is('cms/toursdk/page*')) ? 'active' : '' }}" href="{{ route('route_toursdk_page.index') }}"><i class="far fa-circle"></i> {{ __('Page') }}</a>
            
           
          </div>
        </div>
      </li>

      <!-- ##################################################################### -->