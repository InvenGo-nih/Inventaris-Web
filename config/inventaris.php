<?php

return [

    'groups_per_page' => (int) env('INVENTARIS_GROUPS_PER_PAGE', 8),

    'items_per_page' => (int) env('INVENTARIS_ITEMS_PER_PAGE', 10),

    'overdue_per_page' => (int) env('INVENTARIS_OVERDUE_PER_PAGE', 20),

    'qr_size' => (int) env('INVENTARIS_QR_SIZE', 50),

    'image_max_kb' => (int) env('INVENTARIS_IMAGE_MAX_KB', 10240),

    'pdf' => [
        'rows_per_page' => (int) env('INVENTARIS_PDF_ROWS_PER_PAGE', 7),
        'qr_size' => (int) env('INVENTARIS_PDF_QR_SIZE', 15),
        'line_height' => (int) env('INVENTARIS_PDF_LINE_HEIGHT', 30),
    ],

];
