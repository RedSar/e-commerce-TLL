<?php

return [
    'brand' => [
        'label' => 'Marque',
        'plural' => 'Marques',
        'navigation_group' => 'Gestion des marques',
        'fields' => [
            'name' => 'Nom',
           
        ],
    ],

    'category' => [
        'label' => 'Catégorie',
        'plural' => 'Catégories',
        'navigation_group' => 'Gestion des marques',
        'fields' => [
            'name' => 'Nom',
        ],
    ],

    'orders' => [
        'label' => 'Commandes',
        'plural' => 'Commandes',
        'navigation_group' => 'Gestion des commandes',
        'fields' => [
            'name' => 'Nom',
            'currency' => 'Devise',
            'notes' => 'Notes',
            'phone' => 'Telephone',
            'address' => 'Adresse',
            'shipping_address' => 'Adresse de livraison',
            'shipping_method' => 'Methode de livraison',
        ],
    ],
];