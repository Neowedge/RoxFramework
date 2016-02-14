<?php
namespace RoxFramework;

class RequestResult {
	//OK
	const CODIGO_OK = '0';
	
	//CONTROLLER: 1
	
	
	//BBDD: 500_2|PROCESO
	const CODIGO_BBDD_CONEXION = '500_201';
	const CODIGO_BBDD_TRANSACCION = '500210';
	const CODIGO_BBDD_QUERY = '500_211';
	const CODIGO_BBDD_SQL = '500_220';
	const CODIGO_BBDD_FILTER = '500_230';
	const CODIGO_BBDD_FILTER_OPERADOR = '500_231';
	const CODIGO_BBDD_FILTER_CAMPOS = '500_232';
	const CODIGO_BBDD_ORDER = '500_240';
	const CODIGO_BBDD_ORDER_CLAUSULA = '500_241';
	const CODIGO_BBDD_ORDER_CAMPOS = '500_242';
	
	//PARAMETROS: 100_3|TABLA|CAMPO
	const CODIGO_PARAMETROS_INVALIDOS = '100_300';
	
	//GRADAS VODAFONE: 100_1|ERROR
	const CODIGO_GRADASVDAFONE_MOVILREPETIDO = '100_101';

	//ROX OFFICE
	const CODIGO_ROXOOFICCE_BADFUNCTION = '200_101';

	//PÁGINA NO SE ENCUENTRA
	const CODIGO_404 = '404';
	
	public $cod;
	public $message;
	
	public function __construct($cod=self::CODIGO_OK, $message='ok', $debug=null) {
		$this->cod = $cod;
		$this->message = $message;
		
		if ($debug!=null && ENV!='prod' && DEBUG) {
			$this->debug = $debug;
		}
	}
}