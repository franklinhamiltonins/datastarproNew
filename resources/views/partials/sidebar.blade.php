<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{route('dashboard')}}" class="brand-link py-2 d-flex align-items-center justify-content-center">
        <img src="{{asset('/images/logo.png')}}" alt="DatastarPro Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light ml-2">{{config('app.name')}}</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @can('dashboard-navigation')
                <li class="nav-item">
                    <a href="{{route('dashboard')}}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt nav-icon"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                @endcan


                @can('lead-navigation')
                <li class="nav-item has-treeview {{ (request()->routeIs('leads.*') || request()->routeIs('registeragent.*') || request()->routeIs('contacts.*')) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>
                            Business
                            <i class="fas fa-angle-right right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        @can('lead-list')
                        <li class="nav-item">
                            <a href="{{route('leads.index')}}"
                                class="nav-link {{ request()->routeIs('leads.index') ? 'active' : '' }}">
                                <i class="fas fa-business-time nav-icon"></i>
                                <p>All Businesses</p>
                            </a>
                        </li>
                        @endcan
                        @can('register-agent-list')
                        <li class="nav-item">
                            <a href="{{route('registeragent.index')}}"
                                class="nav-link {{ request()->routeIs('registeragent.index') ? 'active' : '' }}">
                                <i class="fas fa-user-shield nav-icon"></i>
                                <p>Registered Agent</p>
                            </a>
                        </li>
                        @endcan
                        @can('contact-list')
                        <li class="nav-item">
                            <a href="{{route('contacts.index')}}"
                                class="nav-link {{ request()->routeIs('contacts.index') ? 'active' : '' }}">
                                <i class="fas fa-address-book nav-icon"></i>
                                <p>All Contacts</p>
                            </a>
                        </li>
                        @endcan

                        @can('lead-create')
                        <li class="nav-item">
                            <a href="{{route('leads.create')}}" class="nav-link">
                                <i class="fa fa-plus nav-icon"></i>
                                <p>Add New Business</p>
                            </a>
                        </li>
                        @endcan
                        @can('lead-import')
                        <li class="nav-item">
                            <a href="{{route('leads.import')}}" class="nav-link">
                                <i class="fas fa-file-upload nav-icon"></i>
                                <p>Import Lead & Contacts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('leads.business')}}" class="nav-link">
                                <i class="fas fa-file-upload nav-icon"></i>
                                <p>Import Businesses Only</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('leads.importContacts')}}" class="nav-link">
                                <i class="fas fa-file-upload nav-icon"></i>
                                <p>Import Lead Contacts</p>
                            </a>
                        </li>

                        @endcan



                    </ul>
                </li>

                @endcan

                @can('campaign-navigation')
                <li class="nav-item has-treeview ">
                    <a href="{{route('campaigns.index')}}"
                        class="nav-link {{ request()->routeIs('campaigns.index') ? 'active' : '' }}">
                        <!-- <i class="fas fa-address-book nav-icon"></i> -->
                        <i class="fas fa-envelope-open-text nav-icon"></i>
                        <p>
                            Mailing Lists
                            {{-- <i class="fas fa-angle-right right nav-icon"></i> --}}
                        </p>
                    </a>
                </li>
                @endcan

                @can('dialing-navigation')
                <li class="nav-item has-treeview {{ (request()->routeIs('dialings.*')) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <!-- <i class="nav-icon fas fa-mobile"></i> -->
                        <i class="fas fa-bars nav-icon"></i>
                        <p>
                            Lists
                            <i class="fas fa-angle-right right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        @can('dialing-list')
                        <li class="nav-item">
                            <a href="{{route('dialings.index')}}"
                                class="nav-link {{ request()->routeIs('dialings.index')  ? 'active' : '' }}">
                                <i class="fas fa-phone-alt nav-icon"></i>
                                <p>Dialing</p>
                            </a>
                        </li>
                        @endcan
                        @can('owned-list')
                        <li class="nav-item">
                            <a href="{{route('dialings.ownedleads')}}"
                                class="nav-link {{ request()->routeIs('dialings.ownedleads') ? 'active' : '' }}">
                                <i class="fas fa-user-tie nav-icon"></i>
                                <p>Owned Leads</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>

                @endcan

                @can('user-navigation')
                <li class="nav-item has-treeview {{ request()->routeIs('users.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Users Management
                            <i class="fas fa-angle-right right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        @can('user-list')
                        <li class="nav-item">
                            <a href="{{route('users.index')}}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                                <i class="fa fa-user nav-icon"></i>
                                <p>All Users</p>
                            </a>
                        </li>
                        @endcan

                        @can('user-create')
                        <li class="nav-item">
                            <a href="{{route('users.create')}}" class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}">
                                <!-- <i class="fa fa-plus nav-icon"></i> -->
                                <i class="fas fa-user-plus nav-icon"></i>
                                <p>Add New User</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('role-navigation')
                <li class="nav-item has-treeview {{ request()->routeIs('roles.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="fas fa-key nav-icon"></i>
                        <p>
                            Role Management
                            <i class="fas fa-angle-right right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('role-list')
                        <li class="nav-item">
                            <a href="{{route('roles.index')}}" class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                                <i class="fas fa-unlock nav-icon"></i>
                                <p>All Roles</p>
                            </a>
                        </li>
                        @endcan
                        @can('role-create')
                        <li class="nav-item">
                            <a href="{{route('roles.create')}}" class="nav-link {{ request()->routeIs('roles.create') ? 'active' : '' }}">
                                <i class="fa fa-plus nav-icon"></i>
                                <p>Add New Role</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('setting-navigation')
                <li class="nav-item has-treeview {{ (request()->routeIs('settings.*') || request()->routeIs('bot.settings') || request()->routeIs('smsprovider.*') || request()->routeIs('*.settings') || request()->routeIs('smtps.index') || request()->routeIs('templates.index') || request()->routeIs('platform_setting.index') || request()->routeIs('scrap_sunbiz') || request()->routeIs('newsletter.index') || request()->routeIs('carrier.*') || request()->routeIs('rating.*') || request()->routeIs('leadsource.*') || request()->routeIs('contactstatus.*') || request()->routeIs('newsletter.*')) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="fas fa-tools nav-icon"></i>
                        <p>
                            Settings
                            <i class="fas fa-angle-right right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('setting-document')
                        <li class="nav-item">
                            <a href="{{route('settings.doc')}}" class="nav-link {{ request()->routeIs('settings.doc') ? 'active' : '' }}">
                                <i class="fas fa-file-alt nav-icon"></i>
                                <p>Documentation</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-system')
                        <li class="nav-item">
                            <a href="{{route('settings.systemsetting')}}" class="nav-link {{ request()->routeIs('settings.systemsetting') ? 'active' : '' }}">
                                <i class="fas fa-cog nav-icon"></i>
                                <p>System</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-contact-status')
                        <li class="nav-item">
                            <a href="{{route('contactstatus.index')}}" class="nav-link {{ request()->routeIs('contactstatus.index') ? 'active' : '' }}">
                                <i class="fas fa-hourglass-half nav-icon"></i>
                                <p>Contact Status</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-carrier')
                        <li class="nav-item">
                            <a href="{{route('carrier.index')}}" class="nav-link {{ request()->routeIs('carrier.index') ? 'active' : '' }}">
                                <i class="fas fa-baby-carriage nav-icon"></i>
                                <p>Carrier</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-rating')
                        <li class="nav-item">
                            <a href="{{route('rating.index')}}" class="nav-link {{ request()->routeIs('rating.index') ? 'active' : '' }}">
                                <i class="fas fa-medal nav-icon"></i>
                                <p>Rating</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-leadsource')
                        <li class="nav-item">
                            <a href="{{route('leadsource.index')}}" class="nav-link {{ request()->routeIs('leadsource.index') ? 'active' : '' }}">
                                <i class="fas fa-bullhorn nav-icon"></i>
                                <p>Lead Source</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-bot')
                        <li class="nav-item">
                            <a href="{{route('bot.settings')}}" class="nav-link {{ request()->routeIs('bot.settings') ? 'active' : '' }}">
                                <i class="fas fa-solid fa-robot nav-icon"></i>
                                <p>Bot</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-smtp')
                        <li class="nav-item">
                            <a href="{{route('smtps.index')}}" class="nav-link {{ request()->routeIs('smtps.index') ? 'active' : '' }}">
                                <i class="fas fa-solid fa-envelope nav-icon"></i>
                                <p>SMTP</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-smsprovider')
                        <li class="nav-item">
                            <a href="{{route('smsprovider.index')}}" class="nav-link {{ request()->routeIs('smsprovider.index') ? 'active' : '' }}">
                                <i class="fas fa-solid fa-envelope-open-text nav-icon"></i>
                                <p>Sms Provider</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-template')
                        <li class="nav-item">
                            <a href="{{route('templates.index')}}" class="nav-link {{ request()->routeIs('templates.index') ? 'active' : '' }}">
                                <i class="fas fa-solid fa-newspaper nav-icon"></i>
                                <p>Templates (SMS & Email)</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-scrapapi')
                        <li class="nav-item">
                            <a href="{{route('platform_setting.index')}}" class="nav-link {{ request()->routeIs('platform_setting.index') ? 'active' : '' }}">
                                <i class="fas fa-solid fa-cogs nav-icon"></i>
                                <p>Scrap Api Platform</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-sunbiz')
                        <li class="nav-item">
                            <a href="{{route('scrap_sunbiz')}}" class="nav-link {{ request()->routeIs('scrap_sunbiz') ? 'active' : '' }}">
                                <i class="fas fa-solid fa-cogs nav-icon"></i>
                                <p>Sunbiz Scrap</p>
                            </a>
                        </li>
                        @endcan
                        @can('setting-newsletter')
                        <li class="nav-item">
                            <a href="{{route('newsletter.index')}}" class="nav-link {{ request()->routeIs('newsletter.index') ? 'active' : '' }}">
                                <i class="fas fa-solid fa-warehouse nav-icon"></i>
                                <p>Newsletter</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                @can('pipe-management')
                <li class="nav-item">
                    <a href="{{ $notifications['pipeline_url'] . '/deal' }}" target="_blank" class="nav-link">
                        <i class="nav-icon fas fa-stream"></i>
                        <p>Pipe Management</p>
                    </a>
                </li>
                @endcan
                @can('bind-management')
                <li class="nav-item">
                    <a href="{{ $notifications['pipeline_url'] . '/bind-mgt' }}" target="_blank" class="nav-link">
                        <i class="nav-icon fas fa-file-signature"></i>
                        <p>Bind Management</p>
                    </a>
                </li>
                @endcan
                @can('report-display')
                <li class="nav-item has-treeview {{ ( request()->routeIs('agentreport.activityReport') || request()->routeIs('agentreport.mailerLeadReport') || request()->routeIs('agentreport.daillyCallReport') ) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="fas fa-file-alt nav-icon"></i>
                        <p>
                            Reports
                            <i class="fas fa-angle-right right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('report-activity-result')
                            <li class="nav-item">
                                <a href="{{route('agentreport.activityReport')}}" class="nav-link {{ request()->routeIs('agentreport.activityReport') ? 'active' : '' }}">
                                    <i class="fas fa-file-alt nav-icon"></i>
                                    <p>Agent Activity</p>
                                </a>
                            </li>
                        @endcan
                        @can('report-mailer-lead-result')
                            <li class="nav-item">
                                <a href="{{route('agentreport.mailerLeadReport')}}" class="nav-link {{ request()->routeIs('agentreport.mailerLeadReport') ? 'active' : '' }}">
                                    <i class="fas fa-chart-line nav-icon"></i>
                                    <p>Mailer Lead Tracker</p>
                                </a>
                            </li>
                        @endcan
                        @can('report-daily-call-result')
                            <li class="nav-item">
                                <a href="{{route('agentreport.daillyCallReport')}}" class="nav-link {{ request()->routeIs('agentreport.daillyCallReport') ? 'active' : '' }}">
                                    <i class="fas fa-phone nav-icon"></i>
                                    <p>Daily Call</p>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                @can('report-tracker-from')
                    <li class="nav-item has-treeview {{ ( request()->routeIs('agentreport.activityIndex') || request()->routeIs('agentreport.mailerLeadIndex') ) ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="fas fa-chart-line nav-icon"></i>
                            <p>
                                Tracker
                                <i class="fas fa-angle-right right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('report-activity-form')
                                <li class="nav-item">
                                    <a href="{{route('agentreport.activityIndex')}}" class="nav-link {{ request()->routeIs('agentreport.activityIndex') ? 'active' : '' }}">
                                        <i class="fas fa-tasks nav-icon"></i>
                                        <p>Agent Activity Form</p>
                                    </a>
                                </li>
                            @endcan
                            @can('report-mailer-lead-form')
                                <li class="nav-item">
                                    <a href="{{route('agentreport.mailerLeadIndex')}}" class="nav-link {{ request()->routeIs('agentreport.mailerLeadIndex') ? 'active' : '' }}">
                                        <i class="fas fa-paper-plane nav-icon"></i>
                                        <p>Mailer Lead Tracker Form</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>

    <!-- /.sidebar -->
</aside>