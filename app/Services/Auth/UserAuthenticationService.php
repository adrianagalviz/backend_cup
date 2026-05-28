<?php

namespace App\Services\Auth;

use App\Models\UsuarioModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserAuthenticationService
{
    public function __construct(
        private readonly InternalTokenService $tokens,
    ) {
    }

    public function findByIdentifier(string $identifier): ?UsuarioModel
    {
        return UsuarioModel::query()
            ->with(['rol', 'persona', 'administrador', 'docente', 'alumno'])
            ->where('nombre_usuario', $identifier)
            ->orWhere('codigo_acceso', $identifier)
            ->orWhereHas('persona', function ($query) use ($identifier): void {
                $query->where('correo', $identifier);
            })
            ->first();
    }

    public function loginTraditional(string $identifier, string $password): array
    {
        $usuario = $this->findByIdentifier($identifier);

        if (!$usuario || !$usuario->activo) {
            throw new RuntimeException('Credenciales invalidas o usuario inactivo.');
        }

        if (!$usuario->password_hash || !password_verify($password, $usuario->password_hash)) {
            throw new RuntimeException('Credenciales invalidas.');
        }

        return $this->authenticatedResponse($usuario);
    }

    public function loginStudentByCode(string $codigoAlumno, ?string $password = null): array
    {
        $usuario = UsuarioModel::query()
            ->with(['rol', 'persona', 'alumno'])
            ->where('codigo_acceso', $codigoAlumno)
            ->orWhereHas('alumno', function ($query) use ($codigoAlumno): void {
                $query->where('codigo_alumno', $codigoAlumno);
            })
            ->first();

        if (!$usuario || !$usuario->activo || $usuario->rol?->nombre !== 'alumno') {
            throw new RuntimeException('Codigo de alumno invalido o usuario inactivo.');
        }

        if ($usuario->password_hash && (!$password || !password_verify($password, $usuario->password_hash))) {
            throw new RuntimeException('Credenciales invalidas.');
        }

        return $this->authenticatedResponse($usuario);
    }

    public function loginFirebase(string $firebaseToken): array
    {
        $firebase = app(FirebaseTokenVerifier::class)->verify($firebaseToken);
        $email = $firebase['email'] ?? null;

        if (!$email) {
            throw new RuntimeException('El token de Firebase no contiene correo valido.');
        }

        $usuario = UsuarioModel::query()
            ->with(['rol', 'persona', 'administrador', 'docente', 'alumno'])
            ->whereHas('persona', function ($query) use ($email): void {
                $query->where('correo', $email);
            })
            ->first();

        if (!$usuario || !$usuario->activo) {
            throw new RuntimeException('El correo verificado no corresponde a un usuario activo del sistema.');
        }

        $uid = $firebase['user_id'] ?? $firebase['sub'] ?? null;

        if ($uid && !$usuario->firebase_uid) {
            $usuario->firebase_uid = $uid;
            $usuario->correo_verificado = (bool) ($firebase['email_verified'] ?? true);
            $usuario->save();
        }

        return $this->authenticatedResponse($usuario->refresh()->load(['rol', 'persona', 'administrador', 'docente', 'alumno']));
    }

    private function authenticatedResponse(UsuarioModel $usuario): array
    {
        DB::table('usuario')
            ->where('id', $usuario->id)
            ->update(['ultimo_inicio_sesion' => now()]);

        return [
            'token' => $this->tokens->generate($usuario),
            'tipo_token' => 'Bearer',
            'usuario' => $this->minimalUserData($usuario),
        ];
    }

    private function minimalUserData(UsuarioModel $usuario): array
    {
        return [
            'id' => $usuario->id,
            'nombre_usuario' => $usuario->nombre_usuario,
            'rol' => $usuario->rol?->nombre,
            'activo' => $usuario->activo,
            'persona' => [
                'id' => $usuario->persona?->id,
                'nombres' => $usuario->persona?->nombres,
                'apellido_paterno' => $usuario->persona?->apellido_paterno,
                'apellido_materno' => $usuario->persona?->apellido_materno,
                'correo' => $usuario->persona?->correo,
            ],
        ];
    }
}
