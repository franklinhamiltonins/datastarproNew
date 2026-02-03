@extends('layouts.app')
@section('pagetitle', 'Assign Team Member' )
@push('breadcrumbs')
<li class="breadcrumb-item"><a href="{{route('users.index')}}">All Users</a></li>
<li class="breadcrumb-item active">Assign Team Member</li>
@endpush
@section('content')
<!-- Main content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 mb-3">
            <div class="pull-right">
                <a class="btn btn-sm btn-primary" href="{{ route('users.index') }}"><i class="fas fa-arrow-circle-left"></i> Back</a>
            </div>
        </div>
    </div>
    <div class="row mt-2 mt-md-4">
        <div class="col-xl-6">
            <div class="card card-secondary">
                <div class="card-header mb-3">
                    <h3 class="card-title">Add Team Member to  {{ $user->name }}</h3>
                </div>
                <form method="POST" action="{{ route('users.updateTeam') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                    <div class="card-body">
                        <div class="form-group">
                            <strong>Select Member:</strong>
                            {!! Form::select("team_member[]", $agentlist, $teams, [
                                "class" => "form-control input",
                                "id" => "team_member",
                                "multiple" => "multiple",
                                "autocomplete" => "off",
                            ]) !!}
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Team Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const master_access = document.getElementById('team_member');
        const gl_choices = new Choices(master_access, {
            removeItemButton: true,  // Show remove button for selected items
            placeholder: true,  // Show placeholder text
            placeholderValue: 'Select Member'
        });
    });
</script>
@endpush
