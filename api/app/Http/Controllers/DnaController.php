<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\models\Dna;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class DnaController
 * @package App\Http\Controllers
 */
class DnaController extends BaseController
{

	public $response;
	public $statusResponse;
	public $dnaMatchs;
	public $dnaAsArray;
	public $horizontal;
	public $oblique;
	public $verticalMatchs;

	/**
	 * __construct
	 */
	public function __construct(){
		$this->response = [
			'messages' => []
		];
		$this->statusResponse = Response::HTTP_BAD_REQUEST;
		$this->dnaMatchs = 0;
		$this->dnaAsArray = [];
		$this->horizontal = 1;
		$this->oblique = 1;
		$this->verticalMatchs = [];


	}
	/**
	 * [mutant description]
	 * @param  Request $request 
	 * @return [json]           
	 */
 		public function isMutant(Request $request){
 			try{
				if($request->has('dna')){
					if($this->checkValidDnaComposition($request->input('dna'))){
						$dnaInDb = Dna::where(['dna' => json_encode($request->input('dna'))])->first();
						if($dnaInDb && (!$dnaInDb->is_mutant)){
							$this->statusResponse = Response::HTTP_FORBIDDEN;
						}else{
							$dnaToSave = [
								'dna' => json_encode($request->input('dna')),
								'is_mutant' => false
							];
							$this->processDna($request->input('dna'));
							if($this->dnaMatchs > 1){
								$dnaToSave['is_mutant'] = true;
								$this->statusResponse = Response::HTTP_OK;
							}else{
								$this->statusResponse = Response::HTTP_FORBIDDEN;
							}
							Dna::createDnaSequence($dnaToSave);
						}
					}
				}else{
					$this->response['messages'][] = 'DNA param not found on request.';
				}
				return response()->json($this->response, $this->statusResponse);
 			}catch(\Exception $e){
				$this->response['messages'][] = 'Something went wrong.Error: ' . $e->getMessage();
				$this->statusResponse = Response::HTTP_INTERNAL_SERVER_ERROR;
 					return response()->json($this->response, $this->statusResponse);
 			}
 		}

	/**
	 * checkValidDnaComposition checks if dna composition is valid to proceed
	 * @param  [array] $dnaArray [dna array string]
	 * @return [boolean]           [result]
	 */
		protected function checkValidDnaComposition($dnaArray){
			$validDnaComposition = true;
			if(!is_array($dnaArray)){
				$this->response['message'][] = 'DNA param must be an array.';
				$validDnaComposition = false;
			}else{
				if(count($dnaArray) != 6){
					$this->response['message'][] = 'DNA param must be an array of 6 strings each value.';
					$validDnaComposition = false;
				}else{
					$dnaStringControl = array_map(function($string){
						return (strlen($string) == 6)? true : false;
					}, $dnaArray);
					if(array_search(false, $dnaStringControl) !== false){
						$this->response['message'][] = 'Each DNA string must have 6 characters.';
						$validDnaComposition = false;
					}
				}
			}
			return $validDnaComposition;
		}

	/**
	 * processDna processs dna array to find matches vertically, horizontally or obliquely
	 * @param  [array] $dna [$dna array]
	 * @return [void]      
	 */
		protected function processDna(Array $dna){
			$this->verticalMatchs = [];
			$this->dnaToArray($dna);
			for ($i=0; $i < 6; $i++) {
				$this->verticalMatchs[$i] = 1;
				for($m=0; $m < 6;$m++){
					if(!isset($this->verticalMatchs[$m])) $this->verticalMatchs[$m] = 1;
					if($m<5) $this->verifyHorizontal($i,$m);
					if($i > 0) $this->verifyVertical($i,$m);
				}
				if($i > 0) $this->verifyOblique($i);
			}

		}

		/**
		 * dnaToArray converts strings inside arrya into array
		 * @param  [array] $dna [$dna array strings]
		 * @return [array]      [$dna astrings to array]
		 */
		protected function dnaToArray(Array $dna){

			$this->dnaAsArray = array_map(function($stringDna){
				return str_split($stringDna);
			},$dna);
		}

		/**
		 * verifyHorizontal verify if dna sequence matchs horizontally
		 *
		 * @param [integer] $i
		 * @param [integer] $m
		 * @return void
		 */
		protected function verifyHorizontal($i,$m){

			if($this->dnaAsArray[$i][$m] == $this->dnaAsArray[$i][$m + 1]){
				$this->horizontal += 1;
				if($this->horizontal == 4){
					$this->dnaMatchs += 1;
				} 
			}else{
				$this->horizontal = 1;
			}
		}

		/**
		 * verifyVertical verify if dna sequence matchs vertically
		 *
		 * @param [integer] $i
		 * @param [integer] $m
		 * @return void
		 */
		protected function verifyVertical($i,$m){

			if($this->dnaAsArray[$i -1][$m] == $this->dnaAsArray[$i][$m]){
				$this->verticalMatchs[$m] += 1;
				if($this->verticalMatchs[$m] == 4) $this->dnaMatchs += 1;
			}else{
				$this->verticalMatchs[$m] = 1;
			}
		}

		/**
		 * verifyOblique verify if dna sequence matchs obliquely
		 *
		 * @param [integer] $i
		 * @return void
		 */
		protected function verifyOblique($i){

			if($this->dnaAsArray[$i -1][$i-1] == $this->dnaAsArray[$i][$i]){
				$this->oblique += 1;
				if($this->oblique == 4) $this->dnaMatchs += 1;
			}else{
				$this->oblique = 1;
			}
		}


		/**
		 * stats returns statistics of dna samples
		 * @param  Request $request 
		 * @return json        
		 */
			public function stats(Request $request){

				try{
					$stats = [];
					$stats['count_mutant_dna'] = Dna::where(['is_mutant' => true])->count();
					$stats['count_human_dna'] = Dna::where(['is_mutant' => false])->count();
					$stats['ratio'] = $this->ratio($stats['count_mutant_dna'], $stats['count_human_dna']);

					return response()->json($stats, Response::HTTP_OK);
				}catch(\Exception $e){
						return response()->json('Something went wrong.Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
				}
			}

		/**
		 * ratio returns the ratio between mutant and human dna
		 * @param  [integer] $a [quantity of mutant DNA]
		 * @param  [integer] $b [quantity of human DNA]
		 * @return [float]    [ration between mutnat dna and human dna]
		 */
			public function ratio($a, $b) {
					 $_a = abs($a);
					 $_b = abs($b);

					 while ($_b != 0) {

							 $remainder = $_a % $_b;
							 $_a = $_b;
							 $_b = $remainder;
					 }
					 return ($a * 0.01);
			 }
}
