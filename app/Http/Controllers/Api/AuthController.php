<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationHelper;
use App\Http\Controllers\Controller;
use App\Models\UsuarioModel;
use App\Services\Auth\InternalTokenService;
use App\Services\Auth\UserAuthenticationService;
use App\Services\Teachers\TeacherManagementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserAuthenticationService $auth,
        private readonly InternalTokenService $tokens,
        private readonly TeacherManagementService $teachers,
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
            'password' => ['required', 'string'],
        ], [
            'codigo_alumno.required' => 'El codigo del alumno es obligatorio.',
            'password.required' => 'La contrasena es obligatoria.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $datos = $this->auth->loginStudentByCode(
                (string) $request->input('codigo_alumno'),
                (string) $request->input('password')
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
                'configuracion_visual' => $this->visualConfig($usuario),
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

    public function actualizarConfiguracionVisual(Request $request): JsonResponse
    {
        $usuario = $request->attributes->get('usuario_autenticado');

        if (!$usuario instanceof UsuarioModel) {
            return ApiResponse::error('Usuario autenticado requerido.', [], 401);
        }

        $validator = Validator::make($request->all(), [
            'paleta' => ['required', 'string', Rule::in(['rosa', 'verde', 'amarillo', 'azul', 'lila', 'rojo', 'gris'])],
            'modo' => ['required', 'string', Rule::in(['claro', 'oscuro'])],
        ], [
            'paleta.in' => 'La paleta seleccionada no es valida.',
            'modo.in' => 'El modo seleccionado no es valido.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        $usuario->paleta_visual = (string) $request->input('paleta');
        $usuario->modo_visual = (string) $request->input('modo');
        $usuario->save();

        return ApiResponse::success('Configuracion visual actualizada correctamente.', [
            'configuracion_visual' => $this->visualConfig($usuario),
        ]);
    }

    public function subirCvDocente(Request $request): JsonResponse
    {
        $usuario = $request->attributes->get('usuario_autenticado');

        if (!$usuario instanceof UsuarioModel) {
            return ApiResponse::error('Usuario autenticado requerido.', [], 401);
        }

        if (!$usuario->docente) {
            return ApiResponse::error('No tienes permisos para subir el CV de docente.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'cv_pdf' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:10240'],
        ], [
            'cv_pdf.required' => 'El PDF del CV es obligatorio.',
            'cv_pdf.file' => 'El CV debe ser un archivo valido.',
            'cv_pdf.mimes' => 'El CV debe ser un archivo PDF.',
            'cv_pdf.mimetypes' => 'El CV debe ser un archivo PDF.',
            'cv_pdf.max' => 'El PDF del CV no debe superar 10 MB.',
        ]);

        if ($validator->fails()) {
            return ValidationHelper::failed($validator);
        }

        try {
            $teacher = $this->teachers->uploadCvPdf($usuario->docente->id, $request->file('cv_pdf'));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        } catch (Throwable) {
            return ApiResponse::error('No se pudo subir el CV a Cloudinary. Verifica tu conexion e intenta nuevamente.', [], 422);
        }

        return ApiResponse::success('CV del docente subido correctamente.', [
            'docente' => $this->teachers->formatTeacher($teacher),
        ]);
    }

    private function roleSpecificData(UsuarioModel $usuario): array
    {
        return match ($usuario->rol?->nombre) {
            'administrador' => [
                'administrador' => $usuario->administrador,
            ],
            'docente' => [
                'docente' => $usuario->docente ? $this->teachers->formatTeacher($usuario->docente) : null,
            ],
            'alumno' => [
                'alumno' => $usuario->alumno,
            ],
            default => [],
        };
    }

    private function visualConfig(UsuarioModel $usuario): array
    {
        return [
            'paleta' => $usuario->paleta_visual ?: 'azul',
            'modo' => $usuario->modo_visual ?: 'claro',
        ];
    }
}
