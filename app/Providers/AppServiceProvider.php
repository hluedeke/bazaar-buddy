<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use \App\AppSettings;
use Validator;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{

		// A custom validator that checks an array size against another array size
		// Useful for an array of radio inputs, to verify the radio inputs are all selected.
		Validator::extend('array_size', function($attribute, $value, $parameters) {
			return sizeof($value) == $parameters[0];
		});

		// A custom validator that checks for dollar format
		Validator::extend('dollar_format', function ($attribute, $value, $parameters) {
			$test = preg_replace("/([^0-9\\.])/i", "", $value);
			return is_numeric($test) && !is_null($test);
		});
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);
	}

}
