<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\User;

class UserSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
	    DB::table('users')->delete();
		
	    $user = new User;
		$user->name = 'Administrator';
		$user->type = 'chair';
		$user->username = 'admin';
		$user->password = password_hash("password", PASSWORD_BCRYPT, ['cost' => 5]);
		$user->save();
		
	}

}

?>