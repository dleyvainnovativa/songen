<?php

/*
|--------------------------------------------------------------------------
| Historia Clínica — Subtipos (extensión por tipo de paciente)
|--------------------------------------------------------------------------
|
| Cada paciente tiene un `tipo_paciente` (Quirúrgico | Neurológico | Geriátrico).
| La Historia Clínica es 1:1 con el paciente, y su "Paso 4" incluye una
| extensión cuyos campos dependen de ese tipo. Este archivo es la única
| fuente de verdad que mapea:
|
|   tipo_paciente  →  tabla hijo (hc_*)  →  modelo  →  campos del formulario
|
| Lo consumen:
|   - HistoriaClinicaService  (decide en qué tabla hijo escribir)
|   - El controlador de la vista (para inyectar la metadata a la blade)
|   - Las Form Requests (para validar solo los campos del subtipo correcto)
|
| Así, agregar/quitar un campo de un subtipo se hace en UN solo lugar.
|
| Tipos de campo soportados por la blade dinámica:
|   text | textarea | number | select | bool | scale
|
| 'scale' = number con etiqueta interpretativa en vivo (Glasgow, Barthel, etc.)
|           'ranges' => [[min, max, 'Etiqueta'], ...]
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | QUIRÚRGICO  →  hc_quirurgicas
    |----------------------------------------------------------------------
    */
    'Quirúrgico' => [
        'tabla'      => 'hc_quirurgicas',
        'modelo'     => \App\Models\HcQuirurgica::class,
        'fk'         => 'id_historia',
        'pk'         => 'id_hc_quirurgica',
        'ui'         => [
            'clase' => 'qx',
            'icono' => 'fa-user-doctor',
            'color' => '#d97706',
            'titulo' => 'Extensión Quirúrgica',
        ],
        'campos' => [
            'clasificacion_asa' => [
                'tipo'  => 'select',
                'label' => 'Clasificación ASA',
                'col'   => 'col-md-4',
                'opciones' => [
                    1 => 'ASA I — Sano',
                    2 => 'ASA II — Enf. sistémica leve',
                    3 => 'ASA III — Enf. sistémica grave',
                    4 => 'ASA IV — Amenaza constante a la vida',
                    5 => 'ASA V — Moribundo',
                ],
            ],
            'riesgo_quirurgico' => [
                'tipo'  => 'select',
                'label' => 'Riesgo quirúrgico',
                'col'   => 'col-md-4',
                'opciones' => [
                    'Bajo' => 'Bajo',
                    'Moderado' => 'Moderado',
                    'Alto' => 'Alto',
                    'Muy alto' => 'Muy alto',
                ],
            ],
            'tipo_cirugia' => [
                'tipo'  => 'text',
                'label' => 'Tipo de cirugía',
                'col'   => 'col-md-4',
                'placeholder' => 'Ej. Artroplastia de rodilla',
            ],
            'procedimiento_previsto' => [
                'tipo'  => 'textarea',
                'label' => 'Procedimiento previsto',
                'col'   => 'col-12',
                'rows'  => 2,
                'placeholder' => 'Descripción del procedimiento planeado',
            ],
            'anestesia_tipo' => [
                'tipo'  => 'text',
                'label' => 'Tipo de anestesia',
                'col'   => 'col-md-6',
                'placeholder' => 'Ej. General balanceada, Regional',
            ],
            'ayuno_horas' => [
                'tipo'  => 'number',
                'label' => 'Ayuno (horas)',
                'col'   => 'col-md-6',
                'min'   => 0,
                'max' => 48,
            ],
            'profilaxis_antibiotica' => [
                'tipo'  => 'bool',
                'label' => 'Profilaxis antibiótica',
                'icono' => 'fa-pills',
                'detalle' => 'profilaxis_detalle',
            ],
            'profilaxis_detalle' => [
                'tipo'  => 'text',
                'label' => 'Detalle profilaxis',
                'col'   => 'col-md-12',
                'oculto' => true,
                'placeholder' => 'Antibiótico, dosis y vía',
            ],
            'alergias_latex' => [
                'tipo'  => 'bool',
                'label' => 'Alergia al látex',
                'icono' => 'fa-hand-dots',
            ],
            'coagulopatia' => [
                'tipo'  => 'bool',
                'label' => 'Coagulopatía',
                'icono' => 'fa-droplet',
                'detalle' => 'coagulopatia_detalle',
            ],
            'coagulopatia_detalle' => [
                'tipo'  => 'text',
                'label' => 'Detalle coagulopatía',
                'col'   => 'col-md-12',
                'oculto' => true,
            ],
            'observaciones' => [
                'tipo'  => 'textarea',
                'label' => 'Observaciones',
                'col'   => 'col-12',
                'rows'  => 2,
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | NEUROLÓGICO  →  hc_neurologicas
    |----------------------------------------------------------------------
    */
    'Neurológico' => [
        'tabla'      => 'hc_neurologicas',
        'modelo'     => \App\Models\HcNeurologica::class,
        'fk'         => 'id_historia',
        'pk'         => 'id_hc_neurologica',
        'ui'         => [
            'clase' => 'neuro',
            'icono' => 'fa-brain',
            'color' => '#0891b2',
            'titulo' => 'Extensión Neurológica',
        ],
        'campos' => [
            'escala_glasgow' => [
                'tipo'  => 'scale',
                'label' => 'Escala de Glasgow',
                'col'   => 'col-md-4',
                'min'   => 3,
                'max' => 15,
                'ranges' => [
                    [13, 15, '✓ Leve (13–15)'],
                    [9, 12, '⚠ Moderado (9–12)'],
                    [3, 8, '✕ Severo (3–8)'],
                ],
            ],
            'estado_mental' => [
                'tipo'  => 'text',
                'label' => 'Estado mental',
                'col'   => 'col-md-4',
                'placeholder' => 'Ej. Alerta, orientado x3',
            ],
            'lenguaje' => [
                'tipo'  => 'text',
                'label' => 'Lenguaje',
                'col'   => 'col-md-4',
                'placeholder' => 'Ej. Fluente, sin afasia',
            ],
            'marcha' => [
                'tipo'  => 'text',
                'label' => 'Marcha',
                'col'   => 'col-md-6',
            ],
            'reflejos' => [
                'tipo'  => 'text',
                'label' => 'Reflejos',
                'col'   => 'col-md-6',
            ],
            'pares_craneales' => [
                'tipo'  => 'textarea',
                'label' => 'Pares craneales',
                'col'   => 'col-12',
                'rows'  => 2,
            ],
            'fuerza_muscular' => [
                'tipo'  => 'text',
                'label' => 'Fuerza muscular',
                'col'   => 'col-md-6',
                'placeholder' => 'Ej. 5/5 en 4 extremidades',
            ],
            'sensibilidad' => [
                'tipo'  => 'text',
                'label' => 'Sensibilidad',
                'col'   => 'col-md-6',
            ],
            'coordinacion' => [
                'tipo'  => 'text',
                'label' => 'Coordinación',
                'col'   => 'col-md-12',
            ],
            'epilepsia' => [
                'tipo'  => 'bool',
                'label' => 'Epilepsia',
                'icono' => 'fa-bolt',
                'detalle' => 'epilepsia_detalle',
            ],
            'epilepsia_detalle' => [
                'tipo'  => 'text',
                'label' => 'Detalle epilepsia',
                'col'   => 'col-md-12',
                'oculto' => true,
            ],
            'deterioro_cognitivo' => [
                'tipo'  => 'bool',
                'label' => 'Deterioro cognitivo',
                'icono' => 'fa-puzzle-piece',
            ],
            'deterioro_escala' => [
                'tipo'  => 'text',
                'label' => 'Escala aplicada',
                'col'   => 'col-md-12',
                'placeholder' => 'Ej. MoCA, MMSE',
            ],
            'deterioro_puntaje' => [
                'tipo'  => 'number',
                'label' => 'Puntaje',
                'col'   => 'col-md-12',
                'min'   => 0,
                'max' => 30,
            ],
            'cefalea_cronica' => [
                'tipo'  => 'bool',
                'label' => 'Cefalea crónica',
                'icono' => 'fa-head-side-virus',
            ],
            'ictus_previo' => [
                'tipo'  => 'bool',
                'label' => 'Ictus previo',
                'icono' => 'fa-brain',
                'detalle' => 'ictus_detalle',
            ],
            'ictus_detalle' => [
                'tipo'  => 'text',
                'label' => 'Detalle ictus',
                'col'   => 'col-md-12',
                'oculto' => true,
            ],
            'observaciones' => [
                'tipo'  => 'textarea',
                'label' => 'Observaciones',
                'col'   => 'col-12',
                'rows'  => 2,
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | GERIÁTRICO  →  hc_geriatricas
    |----------------------------------------------------------------------
    */
    'Geriátrico' => [
        'tabla'      => 'hc_geriatricas',
        'modelo'     => \App\Models\HcGeriatrica::class,
        'fk'         => 'id_historia',
        'pk'         => 'id_hc_geriatrica',
        'ui'         => [
            'clase' => 'geri',
            'icono' => 'fa-person-cane',
            'color' => '#16a34a',
            'titulo' => 'Extensión Geriátrica',
        ],
        'campos' => [
            'escala_barthel' => [
                'tipo'  => 'scale',
                'label' => 'Índice de Barthel (AVD)',
                'col'   => 'col-md-4',
                'min'   => 0,
                'max' => 100,
                'ranges' => [
                    [0, 20, 'Dependencia total'],
                    [21, 60, 'Dependencia severa'],
                    [61, 90, 'Dependencia moderada'],
                    [91, 99, 'Dependencia leve'],
                    [100, 100, 'Independiente'],
                ],
            ],
            'escala_lawton' => [
                'tipo'  => 'scale',
                'label' => 'Escala de Lawton (AIVD)',
                'col'   => 'col-md-4',
                'min'   => 0,
                'max' => 8,
                'ranges' => [
                    [0, 1, 'Dependencia total'],
                    [2, 3, 'Dependencia severa'],
                    [4, 5, 'Dependencia moderada'],
                    [6, 7, 'Dependencia leve'],
                    [8, 8, 'Independiente'],
                ],
            ],
            'escala_tinetti_marcha' => [
                'tipo'  => 'number',
                'label' => 'Tinetti — Marcha',
                'col'   => 'col-md-2',
                'min'   => 0,
                'max' => 12,
            ],
            'escala_tinetti_equilibrio' => [
                'tipo'  => 'number',
                'label' => 'Tinetti — Equilibrio',
                'col'   => 'col-md-2',
                'min'   => 0,
                'max' => 16,
            ],
            'riesgo_caidas' => [
                'tipo'  => 'select',
                'label' => 'Riesgo de caídas',
                'col'   => 'col-md-4',
                'opciones' => [
                    'Bajo' => 'Bajo',
                    'Moderado' => 'Moderado',
                    'Alto' => 'Alto',
                ],
            ],
            'mini_mental_mmse' => [
                'tipo'  => 'scale',
                'label' => 'Mini-Mental (MMSE)',
                'col'   => 'col-md-4',
                'min'   => 0,
                'max' => 30,
                'ranges' => [
                    [0, 9, 'Deterioro severo'],
                    [10, 18, 'Deterioro moderado'],
                    [19, 23, 'Deterioro leve'],
                    [24, 30, 'Normal'],
                ],
            ],
            'escala_depresion_geriatrica' => [
                'tipo'  => 'scale',
                'label' => 'Depresión (Yesavage)',
                'col'   => 'col-md-4',
                'min'   => 0,
                'max' => 15,
                'ranges' => [
                    [0, 4, 'Normal'],
                    [5, 9, 'Depresión leve'],
                    [10, 15, 'Depresión establecida'],
                ],
            ],
            'estado_nutricional' => [
                'tipo'  => 'text',
                'label' => 'Estado nutricional',
                'col'   => 'col-md-4',
                'placeholder' => 'Ej. MNA: normal',
            ],
            'puntaje_nutricional' => [
                'tipo'  => 'number',
                'label' => 'Puntaje nutricional',
                'col'   => 'col-md-2',
                'min'   => 0,
                'max' => 30,
            ],
            'numero_medicamentos' => [
                'tipo'  => 'number',
                'label' => 'N.º de medicamentos',
                'col'   => 'col-md-6',
                'min'   => 0,
                'max' => 30,
                'onchange' => 'App.geri.checkPolifarmacia(this.value)',
                'badge' => 'poly-badge',
            ],
            'polifarmacia' => [
                'tipo'  => 'bool',
                'label' => 'Polifarmacia',
                'icono' => 'fa-prescription-bottle',
            ],
            'red_apoyo_social' => [
                'tipo'  => 'textarea',
                'label' => 'Red de apoyo social',
                'col'   => 'col-12',
                'rows'  => 2,
            ],
            'cuidador_primario' => [
                'tipo'  => 'text',
                'label' => 'Cuidador primario',
                'col'   => 'col-md-12',
            ],
            'vive_solo' => [
                'tipo'  => 'bool',
                'label' => 'Vive solo',
                'icono' => 'fa-house-user',
            ],
            'tipo_vivienda' => [
                'tipo'  => 'text',
                'label' => 'Tipo de vivienda',
                'col'   => 'col-md-12',
            ],
            'sindrome_fragilidad' => [
                'tipo'  => 'bool',
                'label' => 'Síndrome de fragilidad',
                'icono' => 'fa-feather',
            ],
            'sarcopenia' => [
                'tipo'  => 'bool',
                'label' => 'Sarcopenia',
                'icono' => 'fa-dumbbell',
            ],
            'incontinencia_urinaria' => [
                'tipo'  => 'bool',
                'label' => 'Incontinencia urinaria',
                'icono' => 'fa-toilet',
            ],
            'ulceras_presion' => [
                'tipo'  => 'bool',
                'label' => 'Úlceras por presión',
                'icono' => 'fa-bandage',
            ],
            'observaciones' => [
                'tipo'  => 'textarea',
                'label' => 'Observaciones',
                'col'   => 'col-12',
                'rows'  => 2,
            ],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | OTRO  →  sin extensión
    |----------------------------------------------------------------------
    |
    | Paciente genérico. No tiene tabla hija ni campos de subtipo: el paso 4
    | del wizard no aplica. 'tabla', 'modelo' y 'fk' van en null para que el
    | servicio sepa que no debe escribir ninguna extensión, y 'campos' vacío
    | para que la validación y el render no generen nada.
    |
    */
    'Otro' => [
        'tabla'  => null,
        'modelo' => null,
        'fk'     => null,
        'pk'     => null,
        'ui'     => [
            'clase'  => 'otro',
            'icono'  => 'fa-user',
            'color'  => '#64748b',
            'titulo' => 'Sin extensión',
        ],
        'campos' => [],
    ],

];
