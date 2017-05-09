<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use Hash;
use Auth;
use JWTAuth;  
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

class ApiController extends Controller
{
    
	/**
	 * Registra a un nuevo cliente 
	 * Valida que su correo electronico no halla sido utilizado
	 * @param  Request $request [Datos del formulario]
	 * @return [Authenticate , view]
	 */
    public function register(Request $request){
    	
    	$messages = ['email.unique' => 'El correo '.$request->input('email') . ' ya esta registrado' ];

    	 $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users',
        ],$messages);

        if ($validator->fails()) {
        	return response()->json(['status' => 'fail',
        		'message' => 'Error ya existe el correo electronico  en nuestros registros']);
        }

    	$user = new User();
    	$user->name = $request->input('name');
    	$user->email = $request->input('email');
    	$user->password = bcrypt($request->input('password'));
    	$user->save();
    
       return response()->json(['status' => 'success',
        		'message' => 'Te haz registarado con exito ' .$user->name ]);

    }

    /**
     * Logea a un cliente con sus credenciales
     * @param  Request $request [Json post request]
     * @return [json]           [Token de autenticacion]
     */
    public function login (Request $request) {

         $credentials = $request->only('email', 'password');

          try {
              // Verifica las credenciales del cliente
              if (! $token = JWTAuth::attempt($credentials)) {
                  return response()->json(['error' => 'invalid_credentials'], 401);
              }
          } catch (JWTException $e) {
              // Algo salio mal tratando de acceder al servidor
              return response()->json(['error' => 'could_not_create_token'], 500);
          }

          // Compacta y regresa el token de autenticacion
          return response()->json(compact('token'));

    }

    /**
     * Destruye la session utilizando el token de authenticacion
     * @param  [type] $token [json token]
     * @return [boolean]
     */
    public function logout (){
       JWTAuth::invalidate( $_GET['token'] );
       return "Haz finalizado session correctamente";
    }

    public function userDashboard(){
    	return "Estas logueado como " . Auth::user()->name;
    }



}
