<footer class="main-footer">
    <div class="container-fluid">
        <div class="pl-2 d-flex justify-content-between text-dark small">
            <p class="mb-0">Copyright © {{ now()->year }} - <strong>{{env('APP_NAME')}}</strong>. All rights reserved.</p>
            <div class="f-nav">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item"><a href="{{route('privacy-policy')}}">Privacy Policy</a></li>
                    <li class="list-inline-item"><a href="{{route('terms-condition')}}">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </div>
    </div>
</footer>