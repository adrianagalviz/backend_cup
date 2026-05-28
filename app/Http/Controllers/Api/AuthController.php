<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\UsuarioModel;
use App\Services\Auth\InternalTokenService;
use App\Services\Auth\UserAuthenticationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserAuthenticationService $auth,
        private readonly InternalTokenService $tokens,
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'usuario.required' => 'El usuario, correo o codigo es obligatorio.',
            'password.required' => 'La contrasena es obligatoria.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $datos = $this->auth->loginTraditional(
                (string) $request->input('usuario'),
                (string) $request->input('password')
            );

            return ApiResponse::success('Inicio de sesion correcto.', $datos);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 401);
        }
    }

    public function loginAlumno(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo_alumno' => ['required', 'string'],
            'password' => ['nullable', 'string'],
        ], [
            'codigo_alumno.required' => 'El codigo del alumno es obligatorio.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $datos = $this->auth->loginStudentByCode(
                (string) $request->input('codigo_alumno'),
                $request->filled('password') ? (string) $request->input('password') : null
            );

            return ApiResponse::success('Inicio de sesion de alumno correcto.', $datos);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 401);
        }
    }

    public function firebase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firebase_token' => ['required', 'string'],
        ], [
            'firebase_token.required' => 'El token de Firebase es obligatorio.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $datos = $this->auth->loginFirebase((string) $request->input('firebase_token'));

            return ApiResponse::success('Inicio de sesion con Firebase correcto.', $datos);
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->tokens->revoke($request->bearerToken());

        return ApiResponse::success('Sesion cerrada correctamente.');
    }

    public function perfil(Request $request): JsonResponse
    {
        $usuario = $request->attributes->get('usuario_autenticado');

        if (!$usuario instanceof UsuarioModel) {
            return ApiResponse::error('Usuario autenticado requerido.', [], 401);
        }

        return ApiResponse::success('Perfil obtenido correctamente.', [
            'usuario' => [
                'id' => $usuario->id,
                'nombre_usuario' => $usuario->nombre_usuario,
                'codigo_acceso' => $usuario->codigo_acceso,
                'correo_verificado' => $usuario->correo_verificado,
                'activo' => $usuario->activo,
            ],
            'rol' => [
                'id' => $usuario->rol?->id,
                'nombre' => $usuario->rol?->nombre,
            ],
            'persona' => [
                'id' => $usuario->persona?->id,
                'cedula_identidad' => $usuario->persona?->cedula_identidad,
                'nombres' => $usuario->persona?->nombres,
                'apellido_paterno' => $usuario->persona?->apellido_paterno,
                'apellido_materno' => $usuario->persona?->apellido_materno,
                'correo' => $usuario->persona?->correo,
                'celular' => $usuario->persona?->celular,
                'ciudad' => $usuario->persona?->ciudad,
            ],
            'datos_rol' => $this->roleSpecificData($usuario),
        ]);
    }

    private function roleSpecificData(UsuarioModel $usuario): array
    {
        return match ($usuario->rol?->nombre) {
            'administrador' => [
                'administrador' => $usuario->administrador,
            ],
            'docente' => [
                'docente' => $usuario->docente,
            ],
            'alumno' => [
                'alumno' => $usuario->alumno,
            ],
            default => [],
        };
    }
}
