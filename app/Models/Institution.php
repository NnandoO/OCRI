<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country', 'type'];

    /**
     * Relación: Una institución tiene muchos convenios.
     */
    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class);
    }

    /**
     * Mutador automático para la columna 'type'.
     * Evalúa el valor ingresado y el nombre de la entidad para forzar
     * el almacenamiento de una de las 10 categorías oficiales.
     */
    protected function type(): Attribute
    {
        return Attribute::make(
            set: function (string $value, array $attributes) {
                $cleanValue = strtoupper(trim($value));
                // Usamos $attributes porque durante un Institution::create() el modelo aún no se ha guardado
                $cleanName = strtoupper(trim($attributes['name'] ?? ''));

                return match (true) {
                    // 1. Comunidades
                    str_contains($cleanName, 'COMUNIDAD') || str_contains($cleanName, 'NATIVA') => 'Comunidades',

                    // 2. Universidades
                    str_contains($cleanName, 'UNIVERSIDAD') && str_contains($cleanName, 'NACIONAL') => 'Universidad Nacional',
                    str_contains($cleanName, 'UNIVERSIDAD') && str_contains($cleanName, 'PRIVADA') => 'Universidad Privada',
                    str_contains($cleanName, 'UNIVERSIDAD') => 'Universidad Internacional',

                    // 3. Salud
                    str_contains($cleanValue, 'SALUD') || str_contains($cleanName, 'HOSPITAL') || str_contains($cleanName, 'ESSALUD') || str_contains($cleanName, 'CLINICA') || str_contains($cleanName, 'CLÍNICA') || str_contains($cleanName, 'RED DE SALUD') || str_contains($cleanName, 'IREN') => 'Salud',

                    // 4. Educación
                    str_contains($cleanValue, 'EDUCACIÓN') || str_contains($cleanValue, 'EDUCACION') || str_contains($cleanName, 'I.E.') || str_contains($cleanName, 'COLEGIO') || str_contains($cleanName, 'INSTITUTO') || str_contains($cleanName, 'CETPRO') || str_contains($cleanName, 'EESP') || str_contains($cleanName, 'IESTP') => 'Educación',

                    // 5. Sector Público
                    str_contains($cleanValue, 'MUNICIPALIDAD') || str_contains($cleanName, 'MUNICIPALIDAD') || str_contains($cleanName, 'MINISTERIO') || str_contains($cleanName, 'GOBIERNO REGIONAL') || str_contains($cleanName, 'INDECOPI') || str_contains($cleanName, 'INPE') || str_contains($cleanName, 'PROVIAS') || str_contains($cleanName, 'SUNAFIL') || str_contains($cleanName, 'INEI') || str_contains($cleanName, 'SERFOR') || str_contains($cleanName, 'ANA') || str_contains($cleanName, 'CEPLAN') || str_contains($cleanName, 'SENAMHI') || str_contains($cleanName, 'OEFA') || str_contains($cleanName, 'INIA') => 'Sector Público',

                    // 6. Empresas
                    str_contains($cleanValue, 'EMPRESA NACIONAL') || str_contains($cleanName, 'SAC') || str_contains($cleanName, 'EIRL') || str_contains($cleanName, 'S.R.L.') || str_contains($cleanName, 'S.A.') || str_contains($cleanName, 'SCRL') => 'Empresa Nacional',
                    str_contains($cleanValue, 'EMPRESA INTERNACIONAL') => 'Empresa Internacional',

                    // 7. Por Defecto
                    default => 'Otros',
                };
            }
        );
    }
}