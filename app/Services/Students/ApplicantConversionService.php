<?php

namespace App\Services\Students;

use App\Models\AlumnoModel;
use App\Models\PagoStripeModel;
use App\Models\PostulanteModel;
use App\Models\UsuarioModel;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApplicantConversionService
{
    public function convertToStudent(int $postulanteId, UsuarioModel $administrator): AlumnoModel
    {
        return DB::transaction(function () use ($postulanteId, $administrator): AlumnoModel {
            $postulante = PostulanteModel::query()
                ->with(['persona', 'gestionAcademica', 'pagoStripe'])
                ->findOrFail($postulanteId);

            $this->validateApplicantCanBeConverted($postulante);

            $studentCode = $this->generateStudentCode($postulante);
            $studentRoleId = DB::table('rol')->where('nombre', 'alumno')->where('activo', true)->value('id');

            if (! $studentRoleId) {
                throw new RuntimeException('No existe un rol alumno activo.');
            }

            $userId = DB::table('usuario')->insertGetId([
                'persona_id' => $postulante->persona_id,
                'rol_id' => $studentRoleId,
                'nombre_usuario' => 'alumno_'.$studentCode,
                'codigo_acceso' => $studentCode,
                'correo_verificado' => false,
                'password_hash' => null,
                'activo' => true,
                'creado_por_usuario_id' => $administrator->id,
                'creado_en' => now(),
            ]);

            $studentId = DB::table('alumno')->insertGetId([
                'persona_id' => $postulante->persona_id,
                'usuario_id' => $userId,
                'postulante_id' => $postulante->id,
                'gestion_academica_id' => $postulante->gestion_academica_id,
                'codigo_alumno' => $studentCode,
                'estado_academico' => 'activo',
                'creado_en' => now(),
            ]);

            DB::table('postulante')
                ->where('id', $postulante->id)
                ->update([
                    'estado_postulante' => 'habilitado_alumno',
                    'actualizado_en' => now(),
                ]);

            return $this->findStudent($studentId);
        });
    }

    public function validateApplicantCanBeConverted(PostulanteModel $postulante): void
    {
        if ($postulante->estado_requisitos !== 'aprobado') {
            throw new RuntimeException('El postulante debe tener requisitos aprobados.');
        }

        $payment = PagoStripeModel::query()
            ->where('postulante_id', $postulante->id)
            ->first();

        if (! $payment || $payment->estado_pago !== 'pagado') {
            throw new RuntimeException('El postulante debe tener pago Stripe confirmado.');
        }

        if (! $payment->validado_por_usuario_id || ! $payment->validado_en) {
            throw new RuntimeException('El pago debe estar validado por un administrador.');
        }

        if (AlumnoModel::query()->where('postulante_id', $postulante->id)->exists()) {
            throw new RuntimeException('El postulante ya fue convertido en alumno.');
        }

        if (UsuarioModel::query()->where('persona_id', $postulante->persona_id)->exists()) {
            throw new RuntimeException('La persona del postulante ya tiene un usuario asociado.');
        }
    }

    public function generateStudentCode(PostulanteModel $postulante): string
    {
        $gestion = $postulante->gestionAcademica;

        if (! $gestion) {
            throw new RuntimeException('El postulante no tiene gestion academica asociada.');
        }

        if (! in_array((int) $gestion->numero_gestion, [1, 2], true)) {
            throw new RuntimeException('La gestion academica debe ser 1 o 2 para generar el codigo.');
        }

        $ci = preg_replace('/\D/', '', (string) $postulante->persona?->cedula_identidad);

        if (! $ci) {
            throw new RuntimeException('El postulante no tiene cedula valida para generar el codigo.');
        }

        $code = (string) $gestion->anio.(string) $gestion->numero_gestion.$ci;

        if (AlumnoModel::query()->where('codigo_alumno', $code)->exists()
            || UsuarioModel::query()->where('codigo_acceso', $code)->exists()) {
            throw new RuntimeException('El codigo generado ya existe.');
        }

        return $code;
    }

    public function findStudent(int $id): AlumnoModel
    {
        return AlumnoModel::query()
            ->with(['persona', 'usuario.rol', 'postulante', 'gestionAcademica'])
            ->findOrFail($id);
    }

    public function formatStudent(AlumnoModel $student): array
    {
        return [
            'id' => $student->id,
            'codigo_alumno' => $student->codigo_alumno,
            'estado_academico' => $student->estado_academico,
            'creado_en' => $student->creado_en,
            'persona' => [
                'id' => $student->persona?->id,
                'cedula_identidad' => $student->persona?->cedula_identidad,
                'nombres' => $student->persona?->nombres,
                'apellido_paterno' => $student->persona?->apellido_paterno,
                'apellido_materno' => $student->persona?->apellido_materno,
                'correo' => $student->persona?->correo,
            ],
            'usuario' => [
                'id' => $student->usuario?->id,
                'nombre_usuario' => $student->usuario?->nombre_usuario,
                'codigo_acceso' => $student->usuario?->codigo_acceso,
                'rol' => $student->usuario?->rol?->nombre,
                'activo' => $student->usuario?->activo,
            ],
            'postulante' => [
                'id' => $student->postulante?->id,
                'estado_postulante' => $student->postulante?->estado_postulante,
                'estado_requisitos' => $student->postulante?->estado_requisitos,
                'estado_pago' => $student->postulante?->estado_pago,
            ],
            'gestion_academica' => [
                'id' => $student->gestionAcademica?->id,
                'anio' => $student->gestionAcademica?->anio,
                'numero_gestion' => $student->gestionAcademica?->numero_gestion,
                'nombre' => $student->gestionAcademica?->nombre,
            ],
        ];
    }
}
