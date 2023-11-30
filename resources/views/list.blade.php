@extends('layouts.app')
@section('head')
    @include('layouts.partials.header_section',['title'=>__('Native Theme'),'new_btn'=>'checkKey(this)'])
@endsection
@section('content')
    @include('layouts.partials.min_static',[
				'lists' => [
					[
						'active' => $status=='all'?'active':'',
						'href' => route('seller.theme.list'),
						'title' => __('All'),
						'badge' => $all,
					],
					[
						'active' => $status=='enabled'?'active':'',
						'href' => route('seller.theme.list','status=enabled'),
						'title' => __('Enabled'),
						'badge' => $enabled,
					],
					[
						'active' => $status=='disabled'?'active':'',
						'href' => route('seller.theme.list','status=disabled'),
						'title' => __('Disabled'),
						'badge' => $disabled,
					],
			]])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('layouts.partials.search_form',[
						'batch_form_link'=>route('seller.theme.batch'),
						'batch_options' => [
							'enable' => __('Batch Enable'),
							'disable' => __('Batch Disable'),
							'delete' => __('Delete Permanently'),
						],
					])
                    <div class="table-responsive custom-table">
                        <table class="table">
                            <thead>
                            <tr>
                                <th class="am-select" width="10%">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input checkAll" id="customCheck12">
                                        <label class="custom-control-label checkAll" for="customCheck12"></label>
                                    </div>
                                </th>
                                <th class="text-left">{{ __('Logo') }}</th>
                                <th class="text-left">{{ __('Name') }}</th>
                                <th class="text-left">{{ __('Description') }}</th>
                                <th>{{ __('Version') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-right">{{ __('Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $row)
                                <tr>
                                    <th>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="ids[]" class="custom-control-input" id="customCheck{{ $row['name'] }}" value="{{ $row['name'] }}">
                                            <label class="custom-control-label" for="customCheck{{ $row['name'] }}"></label>
                                        </div>
                                    </th>
                                    <td><img src="{{ asset($row['logo']) }}" height="50" alt="{{ $row['name'] }}" ></td>
                                    <td>
                                        <a href="javascript:void(0);" onclick='themeDetail(this)' data-row='{{json_encode($row)}}'>{{ $row['name'] }}</a><br/>
										<a href="{{ $row['author']['home'] }}" target="_blank" class="text-secondary mb-2">{{ $row['author']['name'] }}&lt;{{ $row['author']['email'] }}&gt;</a>
                                    </td>
                                    <td>{{ $row['description'] }}</td>
                                    <td><span class="badge badge-info">V{{ $row['version'] }}</span></td>
                                    <td>
                                        @if($row['status'] == 'Enabled') <span class="badge badge-success">{{ __('Enable') }}</span>
                                        @else <span class="badge badge-warning">{{ __('Disable') }}</span>@endif
                                    </td>
                                    <td class="text-right">
                                        @if($row['status'] == 'Enabled')
                                            <a href="javascript:void(0);" class="btn btn-sm btn-theme btn-warning disable-theme" data-name="{{$row['name']}}">{{ __('Disable') }}</a>
											<a href="{{route('seller.theme.config',['theme'=>$row['name']])}}" class="btn btn-sm btn-info config-theme">{{ __('Setting') }}</a>
                                        @else
                                            <a href="javascript:void(0);" class="btn btn-sm btn-theme btn-success enable-theme" data-name="{{$row['name']}}">{{ __('Enable') }}</a>
                                        @endif
                                        <a href="javascript:void(0);" class="btn btn-sm theme-status btn-danger delete-theme" data-name="{{$row['name']}}">{{ __('Delete') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('modal')
    <div class="modal fade" id="themeDetail" tabindex="-1" aria-labelledby="themeDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="themeDetailLabel">{{ __('Theme Detail') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row">
                    <div class="col-12 col-sm-3 col-md-3 col-lg-3">
                        <img class="theme-image" id="theme-image" src="" alt="" width="100%"/>
                    </div>
                    <div class="col-12 col-sm-9 col-md-9 col-lg-9">
                        <h4><a href="" target="_blank" id="theme-title"></a></h4>
                        <div class="theme-description" style="min-height: 100px;">
                            <p id="theme-description"></p>
                        </div>
                        <div class="theme-action text-right">
                            <a href="javascript:void(0);" class="btn btn-sm theme-status btn-success enable-theme" data-name="" id="theme-status"></a>
                            <a href="javascript:void(0);" class="btn btn-sm theme-status btn-danger delete-theme" data-name="">{{ __('Delete') }}</a>
                        </div>
                    </div>
                    <div id="theme-readme" class="theme-readme"></div>
                </div>
                <div class="modal-footer bg-whitesmoke">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endpush
@push('js')
<script>
	function themeDetail(obj) {
        let item = $(obj).data('row');
        console.log(item);
        $('#theme-image').attr('src',item.logo);
        $('#theme-title').text(item.name);
        $('#theme-title').attr('href',item.home);
        $('#theme-description').text(item.description);
        if (item.status == 'Enabled') {
            $('#theme-status').text('{{ __('Disable') }}').removeClass('btn-success').removeClass('enable-theme').addClass('btn-warning').addClass('disable-theme');
        } else {
            $('#theme-status').text('{{ __('Enable') }}').removeClass('btn-warning').removeClass('disable-theme').addClass('btn-success').addClass('enable-theme');
        }
        $('.theme-status').attr('name',item.home);
        $('#theme-readme').html(item.readme);
		$('#themeDetail').modal('show');
	}
	$('.enable-theme').click(function() {
		const name = $(this).data('name');
		const btn = $('.btn-theme');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: "{{ route('seller.theme.enable') }}",
			type: 'POST',
			data: {
				theme: name,
				_token: "{{ csrf_token() }}"
			},
			beforeSend: function() {
				btn.attr('disabled','').addClass('btn-progress')
				Loading();
			},
			success: function(response) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				Sweet('success',response);
				setTimeout(function(){
					location.reload();
				}, 1500);
			},
			error: function(xhr, status, error) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				$.each(xhr.responseJSON.errors, function (key, item) {
					Sweet('error',item)
				});
			}
		})
	})
	$('.disable-theme').click(function() {
		const name = $(this).data('name');
		const btn = $('.btn-theme');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: "{{ route('seller.theme.disable') }}",
			type: 'POST',
			data: {
				theme: name,
				_token: "{{ csrf_token() }}"
			},
			beforeSend: function() {
				btn.attr('disabled','').addClass('btn-progress')
				Loading();
			},
			success: function(response) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				Sweet('success',response);
				setTimeout(function(){
					location.reload();
				}, 1500);
			},
			error: function(xhr, status, error) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				$.each(xhr.responseJSON.errors, function (key, item) {
					Sweet('error',item)
				});
			}
		})
	})
	$('.delete-theme').click(function() {
		const name = $(this).data('name');
		const btn = $('.btn-theme');
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url: "{{ route('seller.theme.delete') }}",
			type: 'POST',
			data: {
				theme: name,
				_token: "{{ csrf_token() }}"
			},
			beforeSend: function() {
				btn.attr('disabled','').addClass('btn-progress')
				Loading();
			},
			success: function(response) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				Sweet('success',response);
				setTimeout(function(){
					location.reload();
				}, 1500);
			},
			error: function(xhr, status, error) {
				btn.removeAttr('disabled').removeClass('btn-progress')
				Loading();
				$.each(xhr.responseJSON.errors, function (key, item) {
					Sweet('error',item)
				});
			}
		})
	})
</script>
@endpush
