<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

use App\User;

class VolunteerController extends Controller {

	/**
	 * Constructor
	 *
	 * @return void
	 * @author Hannah
	 */
	public function __construct() {
		$this->middleware('vol.auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$volunteers = User::all();
		
		return view('chair.volunteers.index', ['volunteers' => $volunteers]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$volunteer = User::findOrFail($id);
		return view('chair.volunteers.show', ['vol' => $volunteer]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$volunteer = User::findOrFail($id);
		return view('chair.volunteers.edit', ['vol' => $volunteer]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		// Change password
		if($request->input('action') == 'change-password') {
			
			$v = Validator::make($request->all(), [
				'password' => 'required|confirmed|min:6|max:25'
			]);
				
			if($v->fails()) {
				return response()->json($v->getMessageBag()->toArray(), 422);
			}
			
			$password = $request->input('password');
			$vol = User::find($id);
			$vol->password = bcrypt($password);
			$vol->save();
			
			return response()->json(array(
				'message' => 'Your password has been successfully updated.'
			));
		}
		
		// Make user a chair
		if($request->input('action') == 'make-chair') {
			$v = Validator::make($request->all(), [
				'username' => 'required|max:20|unique:users,username',
				'password' => 'required|confirmed|min:6|max:25'
			]);
			
			if($v->fails()) {
				return response()->json($v->getMessageBag()->toArray(), 422);
			}
			
			$vol = User::find($id);
			$vol->password	= bcrypt($request->input('password'));
			$vol->username 	= $request->input('username');
			$vol->type 		= 'chair';
			$vol->save();

			return response()->json(array(
				'message' => "{$vol->name} has been successfully made an admin."
			));
		}
		if($request->input('action') == 'remove-chair') {
			$vol = User::find($id);
			$vol->username 	= NULL;
			$vol->password 	= NULL;
			$vol->type		= 'volunteer';
			$vol->save();
			
			return response()->json(array(
				'message' => "Chair rights for {$vol->name} have successfully been removed."
			));
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
