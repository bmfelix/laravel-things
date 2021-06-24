@extends('structure.main')
@section('content')
    <div class="container-fluid main-container col-12 anodizeContainer" style="padding-top: 20px;">
        <h3>Scan move tag...</h3>
        <div class="col-12">
            <form method="POST" name="anodizeMoveTagScan" id="anodizeMoveTagScan" action="{{ route('postMoveTag') }}" class="col-12">
                <div class="form-group">
                    <div class="col-12">
                        <input id="movetag" type="input" maxlength="7" class="form-control col-12" name="movetag" value="{{ old('movetag') }}" required autofocus placeholder="{{ __('Scan Move Tag') }}" />
                    </div>
                </div>
                @csrf
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('js/moveTag.js') }}"></script>
@endsection
