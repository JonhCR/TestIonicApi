<?php

namespace App\Http\Controllers;

use App\User;
use App\Tareas;
use Auth;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

class ApiController extends Controller
{

    /**
     * Registra a un nuevo cliente
     * Valida que su correo electronico no halla sido utilizado
     * Inicia sesion y devuelve el token de autenticacion
     * 500 = Internal Server Error , 200 = ok , 422 = Unprocessable Entity , 400 Bad request
     * @param  Request $request [Datos del formulario]
     * @return [Authenticate , view]
     */
    public function register(Request $request)
    {

        $messages = ['email.unique' => 'El correo ' . $request->input('email') . ' ya esta registrado'];

        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail',
                'message' => 'Error ya existe el correo electronico  en nuestros registros'] , 422);
        }

        $user           = new User();
        $user->name     = $request->input('name');
        $user->email    = $request->input('email');
        $user->password = bcrypt($request->input('password'));

        //Si el usuario se almacena con éxito inicio sesion y devuelvo el token
        if ($user->save()) {

        	//Crea el arreglo con las credenciales para abrir sesion
            $credentials = array('email' => $request->input('email'),
                              'password' => $request->input('password'));

            try
            {
                $token = JWTAuth::attempt($credentials); // Logea y obtiene el token del usuario
            } catch (JWTException $e) {
                // Error al tratar de crear el token de esta sesion
                return response()->json(['status' => 'fail',
                'message' => 'Error al iniciar session en esta cuenta'] , 500);
            }

            //Retorna la respuesta junto con el token de autenticacion
            return response()->json([
					            	'status' => 'success',
					                'message'=> 'Te haz registarado con exito ' . $user->name,
					                'token'  => $token
					                ],200);
        }//Fin if $user->save()
        //Si el usuario no se almacena bien retorno el error
        else
        {
        	return response()->json(['status' => 'fail',
                'message' => 'Ha ocurrido un error tratando de registrar al usuario'] , 500);
        }//En else $user->save()
    }

    /**
     * Logea a un cliente con sus credenciales
     * @param  Request $request [Json post request]
     * @return [json]           [Token de autenticacion]
     */
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');
        
        try {
            // Verifica las credenciales del cliente
            if (!$token = JWTAuth::attempt($credentials)) {
            	return response()->json(['status' => 'fail',
                'message' => 'Las credenciales son invalidas'] , 401);
            }
        } catch (JWTException $e) {
            // Algo salio mal tratando de crear el token de la sesion
            return response()->json(['status' => 'fail',
                'message' => 'Ocurrió un error tratando de iniciar sesion con esta cuenta'] , 500);
        }

        // Compacta y regresa el token de autenticacion
         return response()->json([
					        'status' => 'success',
					        'message'=> 'Has iniciado session correctamente',
					        'token'  => $token
					        ],200);
    }

    /**
     * Destruye la session utilizando el token de authenticacion
     * @param  [type] $token [json token]
     * @return [boolean]
     */
    public function logout()
    {
        JWTAuth::invalidate($_GET['token']);
        return "Has finalizado session correctamente";
    }

    public function userDashboard()
    {
        return "Estas logueado como " . Auth::user()->name;
    }

    public function getTareas(){
    	return Tareas::where('user_id' , Auth::user()->id )->get();
    }

    public function deleteTareas($id){
    	return Tareas::destroy($id);
    }

    public function createTareas(Request $request){
    	$tarea = new Tareas();
    	$tarea->title = $request->input('title');
    	$tarea->user_id = Auth::user()->id;
        $tarea->save();

        return Tareas::where('user_id' , Auth::user()->id )->get();
    
    }

}
