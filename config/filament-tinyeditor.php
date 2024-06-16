<?php

return [
    'version' => [
        'tiny' => '7.0.1',
        'language' => [
            'version' => '23.10.9',
            'package' => 'langs6',
        ],
        'licence_key' => env('TINY_LICENSE_KEY', 'no-api-key'),
    ],
    'provider' => 'cloud', // cloud|vendor
    // 'direction' => 'rtl',
    /**
     * change darkMode: 'auto'|'force'|'class'|'media'|false|'custom'
     */
    'darkMode' => 'auto',

    /** cutsom */
    'skins' => [
        // oxide, oxide-dark, tinymce-5, tinymce-5-dark
        'ui' => 'oxide',

        // dark, default, document, tinymce-5, tinymce-5-dark, writer
        'content' => 'default'
    ],

    'profiles' => [
        'default' => [
            'plugins' => 'advlist link lists pagebreak searchreplace wordcount fullscreen table',
            'toolbar' => 'fontfamily fontsize | bold italic underline | alignjustify alignleft aligncenter alignright | forecolor backcolor | numlist bullist outdent indent | blockquote table | link | removeformat wordcount fullscreen | undo redo',
            'upload_directory' => null,
            'custom_configs' => [
                'font_family_formats' => 'Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Inter=inter; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
                'font_size_formats' => '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                'font_size_input_default_unit' => 'pt'
            ]
        ],

        'simple' => [
            'plugins' => 'autoresize directionality emoticons link wordcount',
            'toolbar' => 'removeformat | bold italic | rtl ltr | numlist bullist | link emoticons',
            'upload_directory' => null,
            'custom_configs' => [
                'font_family_formats' => 'Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Inter=inter; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
                'font_size_formats' => '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                'font_size_input_default_unit' => 'pt'
            ]
        ],

        'minimal' => [
            'plugins' => 'link wordcount',
            'toolbar' => 'bold italic link numlist bullist',
            'upload_directory' => null,
            'custom_configs' => [
                'font_family_formats' => 'Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Inter=inter; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
                'font_size_formats' => '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                'font_size_input_default_unit' => 'pt'
            ]
        ],

        'full' => [
            'plugins' => 'accordion autoresize codesample directionality advlist autolink link image lists charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons template help',
            'toolbar' => 'undo redo removeformat | fontfamily fontsize fontsizeinput font_size_formats styles | bold italic underline | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist outdent indent accordion | forecolor backcolor | blockquote table toc hr | image link anchor media codesample emoticons | visualblocks print preview wordcount fullscreen help',
            'upload_directory' => null,
            'custom_configs' => [
                'font_family_formats' => 'Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Inter=inter; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
                'font_size_formats' => '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
                'font_size_input_default_unit' => 'pt'
            ]
        ],
    ],

    /**
     * this option will load optional language file based on you app locale
     * example:
     * languages => [
     *      'fa' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@23.10.9/langs6/fa.min.js',
     *      'es' => 'https://cdn.jsdelivr.net/npm/tinymce-i18n@23.10.9/langs6/es.min.js',
     *      'ja' => asset('assets/ja.min.js')
     * ]
     */
    'languages' => [],

    'extra' => [
        'toolbar' => [
            'fontsize' => '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
            'fontfamily' => 'Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Inter=inter; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva; Webdings=webdings; Wingdings=wingdings,zapf dingbats',
        ]
    ]
];
