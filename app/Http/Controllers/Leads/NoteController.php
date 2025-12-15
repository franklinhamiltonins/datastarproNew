<?php

namespace App\Http\Controllers\Leads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\LeadsModel\Lead;
use App\Model\LeadsModel\Note;
use App\Model\LeadsModel\Contact;
use Validator;

class NoteController extends Controller
{

	/**
	 * Get the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function note_show_modal(Request $request, $id)
	{
		//get the note
		$note = Note::find($id);

		if (!$note) {

			toastr()->error('Something went wrong');
			return back();
		}
		//return note data
		return response()->json($note);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function note_update(Request $request, $id)
	{
		$rules = [
			// 'title' => 'required|string|max:191',
			// 'contact_id' => 'required|integer',
			'description' => 'required|string',
		];
		$niceNames = [
			//   'title'=> 'Note Title',
			'contact_id' => 'contact id',
			'description' => 'Note Description'
		];

		//validate fields using nice name in error messages
		$validator = Validator::make($request->all(), $rules, [], $niceNames);

		if ($validator->fails()) {
			$errors = $validator->errors()->all();
			return response()->json(['error' => implode('<br>', $errors)]);
			// return Response()->json($validator);
		}

		try {
			$contact_id = !empty($request->contact_id) ? $request->contact_id : null;
			$description = $request->description;
			//get the note to update
			$note = Note::find($id);
			$prevTitle = $note->title;
			if (!$note) {
				return response()->json(['error' => 'Cand edit this note. It was previously deleted']);
			}
			// dd($note);
			$note->update([
				'contact_id' => $contact_id,
				'description' => $description
			]);

			//get lead
			$lead = Lead::find($note->leads->id);

			create_log($lead, 'Edit Note : ' . $note->title, '');

			return response()->json(['success' => 'Note updated successfully']);
		} catch (\Exception $e) {
			\Log::error('Error creating note: ' . $e->getMessage());
			return response()->json(['error' => $e->getMessage()]);
		}
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function note_store(Request $request, $leadID)
	{	
		// echo "<pre>";print_r($request->input());exit;
		$rules = [
			// 'contact_id' => 'integer',
			'description' => 'required|string',
		];
		$niceNames = [
			// 'title' => 'Call Status',
			'contact_id' => 'Contact ID',
			'description' => 'Note Description'
		];
		$validator = Validator::make($request->all(), $rules, [], $niceNames);
		if ($validator->fails()) {
			$errors = $validator->errors()->all();
			toastr()->error(implode('<br>', $errors));
			return redirect()->back();
		}

		try {

			$input = $request->all();
			$input['user_id'] = auth()->user()->id;
			$note = Note::create($input);
			// getting lead and contact
			$lead = Lead::find($leadID);
			$contact = Contact::find($request->contact_id);

			//attach the note to lead & contact
			$note->leads()->associate($lead);
			$note->contacts()->associate($contact);
			$note->save();
			create_log($lead, 'Create Note : ' . $note->title, '');
			// toastr()->success('Note <b>' . $note->title . '</b> created successfully');
			toastr()->success('Note created successfully');
			return redirect()->back();

		} catch (\Exception $e) {
			\Log::error('Error creating note: ' . $e->getMessage());
			toastr()->error($e->getMessage());
			return back();
		}
	}

	public function updateLeadContactStatus($contact_status, $contact_id)
	{
		$contact_status . '=>' . $contact_id;
		Contact::find($contact_id)->update([
			'status' => $contact_status,
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function note_destroy($id)
	{
		//get the note to delete
		$note = Note::find($id);
		if (!$note) {

			toastr()->error('The note was removed previously');
			return back();
		}
		$lead = Lead::find($note->leads->id);
		create_log($lead, 'Delete Note : ' . $note->title, '');

		$note->delete();
		toastr()->success('Note <b>' . $note->title . '</b> Deleted!');
		return redirect()->back();
	}
}
