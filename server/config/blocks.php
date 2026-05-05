<?php

/*
 * Block type registry.
 *
 * Each entry defines a renderable block type. Admin uses `fields` to render the
 * editing form; the API delivers `data` as JSON to whatever frontend consumes it.
 *
 * Field types: text, textarea, richtext, image, link, boolean, number, select.
 * Mark `translatable: true` for fields stored in block_translations.data per locale.
 *
 * Bloky se přiřazují k webům přes standardní Siteable trait (morphToMany sites).
 */

return [
    'types' => [
        'hero' => [
            'label' => 'Hero',
            'description' => 'Úvodní banner s nadpisem a tlačítkem.',
            'fields' => [
                'image' => [
                    'type' => 'image',
                    'label' => 'Obrázek na pozadí',
                    'translatable' => false,
                ],
                'title' => [
                    'type' => 'text',
                    'label' => 'Nadpis',
                    'translatable' => true,
                    'rules' => 'required|max:200',
                ],
                'subtitle' => [
                    'type' => 'textarea',
                    'label' => 'Podtitul',
                    'translatable' => true,
                ],
                'button_text' => [
                    'type' => 'text',
                    'label' => 'Text tlačítka',
                    'translatable' => true,
                ],
                'button_link' => [
                    'type' => 'text',
                    'label' => 'Odkaz tlačítka',
                    'translatable' => false,
                ],
            ],
        ],
        'about' => [
            'label' => 'O nás',
            'description' => 'Sekce s nadpisem, popisem a obrázkem.',
            'fields' => [
                'image' => [
                    'type' => 'image',
                    'label' => 'Obrázek',
                    'translatable' => false,
                ],
                'title' => [
                    'type' => 'text',
                    'label' => 'Nadpis',
                    'translatable' => true,
                    'rules' => 'required|max:200',
                ],
                'text' => [
                    'type' => 'richtext',
                    'label' => 'Text',
                    'translatable' => true,
                ],
            ],
        ],
        'gallery' => [
            'label' => 'Galerie',
            'description' => 'Galerie fotografií s nadpisem a popisem.',
            'fields' => [
                'images' => [
                    'type' => 'image',
                    'label' => 'Fotografie',
                    'multiple' => true,
                    'translatable' => false,
                ],
                'title' => [
                    'type' => 'text',
                    'label' => 'Nadpis',
                    'translatable' => true,
                    'rules' => 'max:200',
                ],
                'description' => [
                    'type' => 'textarea',
                    'label' => 'Popis',
                    'translatable' => true,
                ],
            ],
        ],
    ],
];
