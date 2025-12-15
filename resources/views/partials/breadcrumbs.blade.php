<div class="content-header">
    <div class="container-fluid">
        <div class="row my-1 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0">@yield('pagetitle')</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    @if(request()->routeIs('dashboard'))
                    <!-- <li class="breadcrumb-item active"><i class="fas fa-home"></i></li> -->
                    @else
                    <!-- <li class="breadcrumb-item"><a href="{{route('dashboard')}}"><i class="fas fa-home"></i></a></li> -->
                    @endif
                    @stack('breadcrumbs')
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>