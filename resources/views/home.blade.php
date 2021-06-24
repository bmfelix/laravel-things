@extends('structure.main')
@section('content')

<div class="container-fluid main-container col-12">
    <div id="root" data-token={{ csrf_token() }}></div>
</div>

@endsection
