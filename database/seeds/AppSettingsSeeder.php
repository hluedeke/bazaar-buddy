<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use \App\AppSettings;
use \App\Bazaar;

class AppSettingsSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
	    DB::table('app_settings')->delete();

		AppSettings::create(['name' => 'current_bazaar', 'setting' => '1']);
		AppSettings::create(['name' => 'credit_card_fee', 'setting' => '1.99']);
		AppSettings::create(['name' => 'bazaar_fee', 'setting' => '16']);
		AppSettings::create(['name' => 'organization', 'setting' => 'Aviano Officers & Civilians Spouses\' Club']);
		
	}

}