<?php

return [
    'column_manager' => [
        'actions' => [
            'reorder' => ['label' => 'Переместить столбец'],
        ],
    ],
    'columns' => [
        'icon' => [
            'boolean' => [
                'true' => 'Да',
                'false' => 'Нет',
            ],
        ],
    ],
    'actions' => [
        'reorder_record' => ['label' => 'Переместить запись :key'],
        'toggle_record_content' => ['label' => 'Развернуть или свернуть запись :key'],
    ],
    'loading' => 'Загрузка…',
    'result_count' => '{0} Нет результатов|{1} :count результат|[2,4] :count результата|[5,*] :count результатов',
];
