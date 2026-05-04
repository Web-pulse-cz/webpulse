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
 * Allowed `blockable` types are listed below — admin restricts the parent picker
 * and the controller validates against this allowlist (no arbitrary class names).
 */

return [
    'allowed_blockables' => [
        'site' => \App\Models\Site\Site::class,
        'page' => \App\Models\Page\Page::class,
        'apartment' => \App\Models\Apartment\Apartment::class,
    ],

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
    ],
];
