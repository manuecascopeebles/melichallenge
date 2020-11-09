<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\models\Dna;
use Carbon\Carbon;

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

	/**
	 * [__construct description]
	 */
	public function __construct(){
		$this->response = [
			'messages' => []
		];
		$this->statusResponse = Response::HTTP_BAD_REQUEST;
		$this->dnaMatchs = 0;
	}
	/**
	 * [mutant description]
	 * @param  Request $request [description]
	 * @return [json]           [description]
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
					$this->response['messages'][] = 'Parametro dna no enviado en el request.';
				}
				return response()->json($this->response, $this->statusResponse);
 			}catch(\Exception $e){
				$this->response['messages'][] = 'Ocurrió un error, vuelva a intentarlo.' . $e->getMessage();
				$this->statusResponse = Response::HTTP_INTERNAL_SERVER_ERROR;
 					return response()->json($this->response, $this->statusResponse);
 			}
 		}

	/**
	 * [checkValidDnaComposition checks if dna composition is valid to proceed]
	 * @param  [array] $dnaArray [dna array string]
	 * @return [boolean]           [result]
	 */
		protected function checkValidDnaComposition($dnaArray){
			$validDnaComposition = true;
			if(!is_array($dnaArray)){
				$this->response['message'][] = 'El parametro dna debe ser un array.';
				$validDnaComposition = false;
			}else{
				if(count($dnaArray) != 6){
					$this->response['message'][] = 'El parametro dna debe ser un array de 6 cadenas.';
					$validDnaComposition = false;
				}else{
					$dnaStringControl = array_map(function($string){
						return (strlen($string) == 6)? true : false;
					}, $dnaArray);
					if(array_search(false, $dnaStringControl) !== false){
						$this->response['message'][] = 'Cada cadena de dna debe tener 6 caracteres.';
						$validDnaComposition = false;
					}
				}
			}
			return $validDnaComposition;
		}

	/**
	 * [processDna description]
	 * @param  [array] $dna [description]
	 * @return [void]      [description]
	 */
		protected function processDna(Array $dna){
			// NO te olvides que oblicuo puede ser para la derecha tmabien!!!!
			$oblique = 1;
			$horizontal = 1;
			$verticalMatchs = [];
			$dnaAsArray = $this->dnaToArray($dna);
			for ($i=0; $i < 6; $i++) {
				$verticalMatchs[$i] = 1;
				for($m=0; $m < 6;$m++){
					if(!isset($verticalMatchs[$m])) $verticalMatchs[$m] = 1;

					if($m<5){
						if($dnaAsArray[$i][$m] == $dnaAsArray[$i][$m + 1]){
							$horizontal += 1;
							if($horizontal == 4) $this->dnaMatchs += 1;
						}else{
							$horizontal = 1;
						}
					}

					if($i > 0){
						if($dnaAsArray[$i -1][$m] == $dnaAsArray[$i][$m]){
							$verticalMatchs[$m] += 1;
							if($verticalMatchs[$m] == 4) $this->dnaMatchs += 1;
						}else{
							$verticalMatchs[$m] = 1;
						}
					}
				}
				if($i > 0){
					if($dnaAsArray[$i -1][$i-1] == $dnaAsArray[$i][$i]){
						$oblique += 1;
						if($oblique == 4) $this->dnaMatchs += 1;
					}else{
						$oblique = 1;
					}
				}
			}

		}

		/**
		 * [dnaToArray description]
		 * @param  [array] $dna [description]
		 * @return [array]      [description]
		 */
		protected function dnaToArray(Array $dna){

			return array_map(function($stringDna){
				return str_split($stringDna);
			},$dna);
		}


		/**
		 * [stats description]
		 * @param  Request $request [description]
		 * @return [json]           [description]
		 */
			public function stats(Request $request){

				try{
					$stats = [];
					$stats['count_mutant_dna'] = Dna::where(['is_mutant' => true])->count();
					$stats['count_human_dna'] = Dna::where(['is_mutant' => false])->count();
					$stats['ratio'] = $this->ratio($stats['count_mutant_dna'], $stats['count_human_dna']);

					return response()->json($stats, Response::HTTP_OK);
				}catch(\Exception $e){
						return response()->json('Ocurrió un error, vuelva a intentarlo.' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
				}
			}

		/**
		 * [ratio description]
		 * @param  [number] $a [description]
		 * @param  [number] $b [description]
		 * @return [float]    [description]
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
