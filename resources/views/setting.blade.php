@extends('layouts.app')
@section('head')
	@include('layouts.partials.header_section',['title'=>__('Theme Config')])
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body">
                <form class="basicform_with_target" data-target="{{ route('seller.theme.list') }}" action="{{ route('seller.theme.config',$theme->name) }}" method="post" >
                    @csrf
                    @foreach($config as $field)
                    
                    @endforeach
                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" > {{ __('Type') }}</label>
                        <div class="col-sm-12 col-md-7">
                            <input type="text" class="form-control" disabled value="{{ $info->type }}">
                            <input type="hidden" name="type" value="{{ $info->type }}">
                        </div>
                    </div>
                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" > {{ __('Client ID') }}</label>
                        <div class="col-sm-12 col-md-7">
                            <input type="text" class="form-control" required="" name="client_id" placeholder="Client ID" value="{{ $info->client_id }}">
                        </div>
                    </div>
                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" > {{ __('Client Secret') }}</label>
                        <div class="col-sm-12 col-md-7">
                            <input type="text" class="form-control" required="" name="client_secret" placeholder="Client Secret" value="{{ $info->client_secret }}">
                        </div>
                    </div>
                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3" > {{ __('Callback Url') }}</label>
                        <div class="col-sm-12 col-md-7">
                            <input type="text" class="form-control" id="callback-url" value="{{ $info->redirect }}" readonly>
                            <small style="color: red"><span>*</span> {{__('Please fill in this link in the callback address')}}</small>
                        </div>
                    </div>
                    <div class="form-group row mb-4">
                        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
                        <div class="col-sm-12 col-md-7">
                            <button class="btn btn-primary basicbtn" type="submit">{{ __('Save') }}</button><br>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
