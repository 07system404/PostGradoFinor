<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migración unificada del sistema PostGrado.
     */
    public function up(): void
    {
        //  1. ESTUDIANTES 
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombres', 50);
            $table->string('paterno', 20);
            $table->string('materno', 20)->nullable();
            $table->string('registro', 20)->unique();
            $table->string('cedula', 20)->unique();
            $table->string('celular', 20)->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->integer('descuento_porcentaje')->default(0);
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        //  2. CURSOS 
        Schema::create('cursos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre', 100);
            $table->string('tipo', 20);            // 'Diplomado' | 'Especialidad' | 'Maestría'
            $table->integer('version');
            $table->integer('edicion');
            $table->string('periodo', 20);

            // Costos del curso
            $table->decimal('costo_matricula', 10, 2);
            $table->decimal('costo_total_estudio', 10, 2);
            $table->decimal('costo_defensa_diplomado', 10, 2);
            $table->decimal('costo_defensa_especialidad', 10, 2);
            $table->decimal('costo_defensa_maestria', 10, 2);

            // Módulos configurables por fase
            $table->integer('nro_modulos_diplomado');
            $table->integer('nro_modulos_especialidad')->nullable();
            $table->integer('nro_modulos_maestria')->nullable();

            $table->integer('cupo');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        //  3. INSCRIPCIONES 
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('curso_id');

            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->cascadeOnDelete();
            $table->foreign('curso_id')->references('id')->on('cursos')->cascadeOnDelete();

            // Tipo de inscripción: hasta qué fase llega el estudiante
            $table->string('tipo_inscripcion', 20)->default('Maestría'); // 'Diplomado' | 'Especialidad' | 'Maestría'

            $table->date('fecha_inscripcion');

            // Estado académico: situación del estudiante en el curso
            // 'Pendiente' | 'Activo' | 'Congelado' | 'Egresado' | 'Retirado'
            $table->string('estado_academico', 20)->default('Pendiente');

            // Estado financiero: situación de sus pagos
            // 'Sin Pagar' | 'Parcial' | 'Al Día' | 'Completado'
            $table->string('estado_financiero', 20)->default('Sin Pagar');

            $table->string('modalidad_pago', 20)->default('Cuotas'); // 'Contado' | 'Cuotas'
            $table->string('observacion', 255)->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'curso_id']);
        });

        //  4. PLAN DE PAGOS 
        Schema::create('plan_pagos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inscripcion_id');

            $table->foreign('inscripcion_id')->references('id')->on('inscripciones')->cascadeOnDelete();

            $table->decimal('monto_total_programado', 10, 2);
            $table->decimal('monto_total_pagado', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);
            $table->integer('total_cuotas');

            // 'Pendiente' | 'Pagado' | 'En Mora' | 'Condonado'
            $table->string('estado', 20)->default('Pendiente');
            $table->timestamps();
        });

        //  5. DETALLE DEL PLAN DE PAGOS 
        Schema::create('detalle_plan_pagos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_pago_id');

            $table->foreign('plan_pago_id')->references('id')->on('plan_pagos')->cascadeOnDelete();

            $table->integer('nro_cuota');
            $table->integer('nro_modulo')->nullable();
            $table->string('concepto', 50);
            $table->string('fase', 20)->nullable();
            $table->decimal('monto_programado', 10, 2);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('monto_descuento', 10, 2)->default(0);
            $table->decimal('saldo_cuota', 10, 2)->default(0);
            $table->date('fecha_vencimiento')->nullable();

            // 'Pendiente' | 'Pagado' | 'Parcial' | 'Vencido' | 'Condonado'
            $table->string('estado', 20)->default('Pendiente');
            $table->timestamps();
        });

        //  6. PAGOS 
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('detalle_plan_pago_id');
            $table->unsignedBigInteger('inscripcion_id');

            $table->foreign('detalle_plan_pago_id')->references('id')->on('detalle_plan_pagos');
            $table->foreign('inscripcion_id')->references('id')->on('inscripciones');

            $table->date('fecha_pago');
            $table->decimal('monto', 10, 2);
            $table->string('nro_comprobante', 50);
            $table->text('observacion')->nullable();
            $table->string('archivo_adjunto')->nullable();
            $table->timestamps();
        });

        //  7. DOCUMENTOS 
        Schema::create('documentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('estudiante_id');

            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->cascadeOnDelete();

            $table->string('tipo', 50);
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 255);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     * Se eliminan en orden inverso para respetar las FK.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('detalle_plan_pagos');
        Schema::dropIfExists('plan_pagos');
        Schema::dropIfExists('inscripciones');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('estudiantes');
    }
};