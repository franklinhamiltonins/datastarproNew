@extends('layouts.app')
@section('pagetitle', 'use App\Traits\CommonFunctionsTrait;')
@push('breadcrumbs')
<li class="breadcrumb-item active">use CommonFunctionsTrait;</li>
@endpush
@section('content')
<section class="content">
	<div class="container-fluid">
		<div class="row mt-2 mt-md-4">
			<div class="col-12 col-sm-12 col-md-12">

				<div class="col-6 col-sm-6 col-md-6">
					<table class="merge-table mb-4 table table-bordered bg-white">
						<thead>
							<tr>
								<th class="bg-gray justify-center">Function</th>
								<th class="bg-gray justify-center">Result</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>1. $this->generateSlug(['abc', 'cde', 'lgf', 'abc']);</td>
								<td class="selected_to_merge_highlight">abc-cde-lgf-abc</td>
							</tr>
							<tr>
								<td>2. $this->calculateDistance("20", "25", "30", "35");</td>
								<td class="selected_to_merge_highlight">1499.1 km</td>
							</tr>
							<tr>
								<td>3. $this->checkLeadSlugExistanceWithDistance("condo-demo-lead-2-howrah-71110", "22.5655352", "88.2862031", '25927');</td>
								<td class="selected_to_merge_highlight"> Array ( [status] => 200 [message] => Array ( ) [existanceCount] => 0 [existingLeads] => Array ( ) )</td>
							</tr>
							<tr>
								<td>4. $this->checkContactSlugExistance("lead-contact2-2578", "138359")</td>
								<td class="selected_to_merge_highlight"> Array ( [status] => 200 [existanceCount] => 0 [existingLeads] => Illuminate\Database\Eloquent\Collection Object ( [items:protected] => Array ( ) [escapeWhenCastingToString:protected] => ) )</td>
							</tr>
							<tr>
								<td>5. $this->removeSpecialCharacters("test$!@#$%^&*~`.:<>,.?/'\"{}[]+-_=1234567890 À Á demo-lorem ipsum")</td>
								<td class="selected_to_merge_highlight">test demo-lorem ipsum</td>
							</tr>
							<tr>
								<td>6. $this->checkContactSlugExistance($contact_slug, $id);</td>
								<td class="selected_to_merge_highlight">Array ( [status] => 200 [existanceCount] => 0 [existingContacts] => Array ( ) )</td>
							</tr>
							<tr>
								<td>7. Generate Permission</td>
								<td class="selected_to_merge_highlight"><a href="/permission/generate" target="_blank">Generate Permission</a></td>
							</tr>
							<tr>
								<td>8. Modal creation</td>
								<td class="selected_to_merge_highlight">
									<button class="btn btn-info btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#contactStatusModal">Change Contact Status</button>
									Refer the above code
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
</section>

@endsection
@push('styles')
@endpush
@push('scripts')
@endpush