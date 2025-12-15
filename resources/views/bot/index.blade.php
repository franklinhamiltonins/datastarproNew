@extends('layouts.app')
@section('pagetitle', 'City Management')
@push('breadcrumbs')

<li class="breadcrumb-item active"><a href="{{route('bot.settings')}}">All Bots</a></li>
<li class="breadcrumb-item active">Bot Management</li>
@endpush
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 margin-tb  mb-3">
                <div class="pull-right">
                    <a class="btn btn-secondary btn-sm" href="{{ route('bot.import') }}"><i class="fas fa-plus-circle"></i>
                        <span class="d-none d-md-inline"> Import Bot
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <div class="table-container pb-4">

            <table class="table table-bordered m-0" id="cityTable">
                <tr>
                    <th style="width:56px;">No</th>
                    <th>Name</th>
                    <th>Search Keyword</th>
                    <th>State</th>
                    <th>County</th>
                </tr>
                @foreach ($cities as $key => $data)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ $data->city }}</td>
                    <td>{{ $data->search_keyword }}</td>
                    <td>{{ $data->state }}</td>
                    <td>{{ optional($data->scrapCounty)->name}}</td>
                </tr>
                @endforeach
            </table>
        </div>


        {!! $cities->render() !!}

    </div><!-- /.container-fluid -->

    @include('partials.delete-modal')
</section>
<!-- /.content -->
@endsection
@push('styles')
@endpush
@push('scripts')
@endpush