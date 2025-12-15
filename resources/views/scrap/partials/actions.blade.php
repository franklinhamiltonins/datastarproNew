<form method="post" class="form-group d-flex" id="form-{{$id}}">
	@csrf

	<input class="form-control rounded-right-0" placeholder="Enter url here..." type="text" name="details_url" id="input_{{$id}}">

	<button class="btn btn-sm btn-primary rounded-left-0 submit_fetch_button" type="button" data-text_id="input_{{$id}}" data-lead_id="{{$lead_id}}">Submit</button>
</form>