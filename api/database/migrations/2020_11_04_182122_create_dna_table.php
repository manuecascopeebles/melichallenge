<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class CreateUserTable
 */
class CreateDnaTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::beginTransaction();
		try {
			Schema::create('dna', function (Blueprint $table) {
				$table->bigIncrements('id');
				$table->text('dna');
				$table->boolean('is_mutant');
			});

			DB::commit();
		} catch (Exception $e) {
			$e->getMessage();
			DB::rollBack();
		}

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}
}
