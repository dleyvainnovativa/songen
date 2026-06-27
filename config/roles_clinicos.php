<?php

/*
|------------------------------------------------------------------------------
| Roles clínicos (puesto del personal médico)
|------------------------------------------------------------------------------
|
| Lista única de valores válidos para la columna `rol` de personal_medico.
| Es la fuente de verdad: la usa la migración (enum), el formulario (select) y
| la validación (Rule::in). Para agregar/quitar un rol, edita aquí y corre la
| migración que ajusta el enum.
|
| OJO: no es lo mismo que `rol_sistema` (admin|medico), que controla el acceso
| al sistema. `rol` es el puesto clínico.
|
*/

return [
    'Médico',
    'Fisioterapeuta',
    'Enfermera/o',
    'Anestesiólogo',
    'Residente',
    'Recepcionista',
    'Administrativo',
    'Otro',
];
