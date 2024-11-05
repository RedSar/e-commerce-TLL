<?php

return [
    'brand' => [
        'label' => 'العلامة التجارية',
        'plural' => 'العلامات التجارية',
        'navigation_group' => 'إدارة العلامات التجارية',
        'fields' => [
            'name' => 'الاسم',
        ],
    ],

    'category' => [
        'label' => 'التصنيف',
        'plural' => 'التصنيفات',
        'navigation_group' => 'إدارة العلامات التجارية',
        'fields' => [
            'name' => 'الاسم',
        ],
    ],

    'orders' => [
        'label' => 'الطلبات',
        'plural' => 'الطلبات',
        'navigation_group' => 'إدارة الطلبات',
        'fields' => [
            'name' => 'الاسم',
            'currency' => 'العملة',
            'phone' => 'التليفون',
            'address' => 'العنوان',
            'payment_method' => 'طريقة الدفع',
            'payment_status' => 'حالة الدفع',
            'status' => 'حالة الطلب',
            'created_at' => 'تاريخ الطلب',
            'updated_at' => 'تاريخ التحديث',
            'deleted_at' => 'تاريخ الحذف',
            'user' => 'المستخدم',
            'items' => 'المنتجات',
            'total' => 'المجموع',
            'shipping_method' => 'طريقة الشحن',
        ],
    ]
];