<?php

namespace App\models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Comments
 * @package App
 */
class Dna extends Model
{

	protected $primaryKey = 'id';
	protected $table = 'dna';
	protected $fillable = [
		'dna',
		'is_mutant'
	];
	public $timestamps = false;

	public static function createDnaSequence($dnafillable){
		$insertRow = new self();
		$insertRow->fill($dnafillable);
		if($insertRow->save()){
			return self::where(['id' => $insertRow->id])->first();
		}else{
			return false;
		}

	}

}
