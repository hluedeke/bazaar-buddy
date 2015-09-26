<?php namespace App\Http\Controllers;

use Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

use App\User;

use Illuminate\Http\Request;


class MainController extends Controller {

	/**
	 * Returns the main view
	 *
	 * @return view
	 * @author Hannah
	 */
	public function index () {
		if(Auth::check())
			return Redirect::to(action('SalesSheetController@index'));
		else
			return view('main\home');
	}
	
	/**
	 * Submits the volunteer login form to give the user access to the 
	 * volunteer area of the site
	 *
	 * @return void
	 * @author Hannah
	 */
	public function login (Request $request)
	{
		$this->validate($request, [
			'name' => 'required'
		]);

		$user = User::whereName($request->input('name'))->first();
		if($user) {
			// Have our chairs actually login
			if($user->isChair())
				return Redirect::to(action('ChairController@index'));
			Auth::login($user);
		}
		else {
			$user = User::create($request->all());
			$user->type = 'volunteer';
			$user->save();
			Auth::login($user);
		}
		
		return Redirect::to(action('MainController@index'));
	}
	
	/**
	 * Performs logout on the user
	 *
	 * @return void
	 * @author Hannah
	 */
	public function logout()
	{
		Auth::logout();
		return Redirect::to(action('MainController@index'));
	}
}
