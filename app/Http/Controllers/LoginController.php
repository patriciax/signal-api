<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class LoginController extends Controller
{
    public function login(Request $request)
	{
		
		$empleado = DB::table("datos_empleados")-> where('cedula', $request-> cedula)-> first();
		if($empleado){
			return response()->json(['mensaje' => 'Exito', 'success' => true, 'nombre' => $empleado -> nombre." ".$empleado -> apellido]);
		}

		else{
			return response()->json(['mensaje' => 'Error, usuario no encontarado', 'success' => false]);
		}

		return response()->json($empleado);
	}

}
