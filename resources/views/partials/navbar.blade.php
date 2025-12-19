<nav class="main-header navbar navbar-expand navbar-white navbar-light justify-content-between pr-4">
    <!-- Left navbar links -->
    <ul class="navbar-nav d-flex align-items-center pl-2 pl-lg-3">
        <li class="nav-item pr-2 pr-lg-3">
            <a class="nav-link p-0" id="toggleSidebar" data-widget="pushmenu" href="javascript:void(0)"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block pr-2 pr-lg-3">
            <a href="{{route('home')}}" class="nav-link p-0"><i class="fas fa-home"></i></a>
        </li>
        @if (Auth::check() && Auth::user()->hasRole('Super Admin'))
        <div class="impersonate position-relative">
            <input type="text" class="form-control" placeholder="Impersonate an agent" name="impersonate"
                id="impersonate_user" />
            <div id="suggestions" class="list-group position-absolute top-100 left-0 right-0 w-100" style="display: none;">
            </div>

        </div>
        @endif
        @if (session()->has('impersonate'))
        <a href="/leave-impersonation" class="lh-1 btn btn-sm btn-outline-primary"> <i
                class="fas fa-arrow-left pr-2"></i> Leave
            Impersonation</a>
        @endif

    </ul>

    <ul class="nav navbar-nav horizontal-nav">
        <li class="dropdown messages-menu open">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="true">
                <i class="fas fa-comments"></i>
                <span class="label  badge label-success" id="notificationList">{{$notifications['msg_count']}}</span>
            </a>
            <ul id="notificationList" class="dropdown-menu main-dropdown">
                @if($notifications['msg_count'] > 0)
                    <li class="footer" id="seeAllmsg"><a href="/notification">See All Messages</a></li>
                @else
                    <li class="footer" id="seeAllmsg"><a href="/notification">See Older Messages</a></li>
                @endif
            </ul>
        </li>
        @if($notifications['can_access_notification'])
            <li class="dropdown messages-menu open">
                 <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fas fa-inbox"></i>
                    <span class="label  badge label-success" id="inboundlist">{{$notifications['inbound_count']}}</span>
                </a>
                <ul id="inboundlist" class="dropdown-menu main-dropdown p-1">
                    
                    @if($notifications['inbound_count'] > 0)
                        <li>
                            <ul class="menu">
                                @foreach($notifications['inbound_messages'] as $message)
                                    <li>
                                        <h4 class="d-flex align-items-center justify-content-between inbould_name">
                                            <a href="/leads/edit/{{base64_encode($message->lead_id)}}" class="pb-0"><span>{{$message->full_name}}</span> </a> <span class="small text-primary">{{date('jS M, y, H:i', strtotime($message->in_time))}}</span>
                                        </h4>
                                        <a href="/smsproviderlist/1/{{$message->contact_id}}"><p class="inbound_content">{{$message->content}}</p></a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        @if($notifications['inbound_count'] > 0)
                            <li class="footer"><a href="/smsproviderlist/1/0">See All Inbound message</a></li>
                        @endif
                    @else
                        <li class="footer"><a href="#">No Inbound Message</a></li>
                    @endif
                </ul>
            </li>
            <li class="dropdown messages-menu open">
                 <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fas fa-envelope"></i>
                    <span class="label  badge label-success" id="inboundlistnotification">{{$notifications['inbound_notification_count']}}</span>
                </a>
                <ul id="inboundlistnotification" class="dropdown-menu main-dropdown p-1">
                    
                    @if($notifications['inbound_notification_count'] > 0)
                        <li>
                            <ul class="menu">
                                @foreach($notifications['inbound_notification_messages'] as $message)
                                    <li>
                                        <h4 class="d-flex align-items-center justify-content-between inbould_name">
                                            <a href="/newsletter/{{$message->newsletter_id}}" class="pb-0"><span>{{$message->full_name}}</span> </a>  <span class="small text-primary">{{date('jS M, y, H:i', strtotime($message->in_time))}}</span>
                                        </h4>
                                        <a href="/smsproviderlist/2/{{$message->newsletter_id}}"><p class="inbound_content">{{$message->content}}</p></a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        @if($notifications['inbound_notification_count'] > 0)
                        <li class="footer"><a href="/newsletter">See All Newsletter Inbound message</a></li>
                        @endif
                    @else
                        <li class="footer"><a href="#">No Newsletter Inbound Message</a></li>
                    @endif
                </ul>
            </li>
        @endif
        @if (Auth::check() && Auth::user()->hasRole('Super Admin'))
            <li class="dropdown messages-menu open">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="true">
                    <i class="fas fa-bell"></i>
                    <span class="label  badge label-success"
                        id="navbarNotification">{{$notifications['call_initiated_count']}}</span>
                </a>
                <ul id="navbarNotification" class="dropdown-menu main-dropdown">
                    <li class="footer p-2 px-3" id="seeAllmsgNavbar"></li>
                    @if($notifications['call_initiated_count'] > 0)
                        <li>
                            <ul class="menu">
                                @foreach($notifications['call_initiated_coll'] as $contact)
                                    <li>
                                        <a href="/leads/edit/{{base64_encode($contact->lead_id)}}">
                                            <h4>
                                                {{$contact->name}}
                                            </h4>
                                            <p>Please update the status of contact : {{$contact->c_full_name}} before making any
                                                updates. </p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        
                            <li class="footer"><a href="/agents/reports">See All Agent Logs</a></li>
                        
                    @endif
                </ul>
            </li>
        @endif

        <li class="dropdown user user-menu open">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="true">
                <i class="fas fa-user d-inline d-lg-none"></i>
                <span class="d-none d-lg-inline" title="">{{ auth()->user()->name }}</span>
            </a>
            <ul class="dropdown-menu" style="left: inherit;right: 0;">
                <li class="user-header">
                    <p class="m-0">
                        {{ auth()->user()->name }}
                        <small>Member since {{ auth()->user()->created_at->format('M, Y') }}</small>
                    </p>
                </li>

                <li class="user-footer">
                    <div class="pull-left float-left">
                        <a href="{{route('profile', auth()->user()->id)}}" class="btn btn-sm btn-default btn-flat px-3 py-2">Profile</a>
                    </div>
                    <div class="pull-right float-right">
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="btn btn-sm btn-default btn-flat px-3 py-2 d-flex align-items-center">Sign out <i class="fas fa-sign-out-alt ml-1"></i></a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </li>
    </ul>
</nav>