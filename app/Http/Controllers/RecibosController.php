<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class RecibosController extends Controller
{
    //
    public function verificar(Request $request){

        $cedula = $request->cedula;
        
        $nominas = DB::table('nomina')
        ->join('conceptos_nominas', 'conceptos_nominas.id_nomina', '=', 'nomina.id')
        ->join('datos_empleados', 'conceptos_nominas.id_empleado', '=', 'datos_empleados.id')
        ->where('datos_empleados.cedula', $cedula)
        ->select('nomina.id')
        ->distinct('nomina.id')
        ->select('nomina.id', 'nomina.fecha_inicio', 'nomina.fecha_fin')
        ->orderBy('nomina.id', 'desc')
        ->get();

        $id_empleado = DB::table('datos_empleados')
        ->where('cedula', $cedula)
        ->pluck('id');

        return response()->json(['nominas' => $nominas, 'id_empleado' => $id_empleado]);
        
    }

    function generar(Request $request){

        $id_empleado = DB::table('datos_empleados') -> where('cedula', $request -> cedula )-> pluck('id');
        //return response()-> json($request->all());
        $datos_empleados = DB::table('datos_empleados')
        ->join('datos_laborales', 'datos_laborales.dato_empleado_id', '=', 'datos_empleados.id')
        ->join('cargos', 'datos_laborales.cargo_id', '=', 'cargos.id')
        ->join('salarios', 'datos_laborales.salario_id', '=', 'salarios.id')
        ->where('datos_empleados.id', $id_empleado)
        ->select('datos_empleados.nombre', 'datos_empleados.cedula', 'datos_empleados.apellido', 'datos_empleados.rif', 'datos_laborales.fecha_ingreso', 'salarios.sueldo', 'cargos.descripcion')
        ->get();
        
        $conceptos = DB::table('conceptos_nominas')
        ->join('conceptos', 'conceptos_nominas.id_concepto', '=', 'conceptos.id')
        ->where('conceptos_nominas.id_nomina', $request->nomina)
        ->where('conceptos_nominas.id_empleado', $id_empleado)
        ->where('conceptos.tipo', '<>', 'patronal')
        ->select('conceptos.descripcion', 'conceptos.tipo', 'conceptos_nominas.monto')
        ->get();
        
        $asignaciones = DB::table('conceptos_nominas')
        ->join('conceptos', 'conceptos_nominas.id_concepto', '=', 'conceptos.id')
        ->where('conceptos_nominas.id_nomina', $request->nomina)
        ->where('conceptos_nominas.id_empleado', $id_empleado)
        ->where('conceptos.tipo', 'asignacion')
        ->sum('conceptos_nominas.monto');
        
        $deducciones = DB::table('conceptos_nominas')
        ->join('conceptos', 'conceptos_nominas.id_concepto', '=', 'conceptos.id')
        ->where('conceptos_nominas.id_nomina', $request->nomina)
        ->where('conceptos_nominas.id_empleado', $id_empleado)
        ->where('conceptos.tipo', 'deduccion')
        ->sum('conceptos_nominas.monto');

        return response()-> json(['datos_empleados'=> $datos_empleados, 'conceptos'=> $conceptos, 'asignaciones'=> $asignaciones, 'deducciones'=> $deducciones]);
    }
}
