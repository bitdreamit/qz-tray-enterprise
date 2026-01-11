<?php

namespace BitDreamIT\QzTray\Helpers;

use Illuminate\Support\Facades\Blade;

if (!function_exists('smartPrintButton')) {
    /**
     * Generate smart print button
     */
    function smartPrintButton(string $url, array $options = []): string
    {
        $defaults = [
            'title' => 'Print',
            'class' => 'btn btn-sm btn-primary smart-print',
            'icon' => 'fas fa-print',
            'iconClass' => '',
            'type' => 'auto',
            'filename' => null,
            'printer' => null,
            'silent' => false,
            'copies' => 1,
            'paperSize' => 'A4',
            'orientation' => 'portrait',
            'attributes' => [],
        ];

        $options = array_merge($defaults, $options);

        // Build attributes
        $attrs = [
            'class' => $options['class'],
            'title' => $options['title'],
            'data-url' => $url,
            'data-type' => $options['type'],
        ];

        if ($options['filename']) {
            $attrs['data-filename'] = $options['filename'];
        }

        if ($options['printer']) {
            $attrs['data-printer'] = $options['printer'];
        }

        if ($options['silent']) {
            $attrs['data-silent'] = 'true';
        }

        if ($options['copies'] > 1) {
            $attrs['data-copies'] = $options['copies'];
        }

        if ($options['paperSize'] !== 'A4') {
            $attrs['data-paper-size'] = $options['paperSize'];
        }

        if ($options['orientation'] !== 'portrait') {
            $attrs['data-orientation'] = $options['orientation'];
        }

        // Add custom attributes
        foreach ($options['attributes'] as $key => $value) {
            if (!isset($attrs[$key])) {
                $attrs[$key] = $value;
            } elseif ($key === 'class') {
                $attrs[$key] .= ' ' . $value;
            }
        }

        // Build HTML
        $iconClass = $options['icon'] . ' ' . $options['iconClass'];

        $html = '<a';
        foreach ($attrs as $key => $value) {
            $html .= ' ' . $key . '="' . e($value) . '"';
        }
        $html .= '>';

        if ($options['icon']) {
            $html .= '<i class="' . e($iconClass) . '"></i>';
        }

        $html .= '</a>';

        return $html;
    }
}

if (!function_exists('smartPrintLink')) {
    /**
     * Generate smart print link
     */
    function smartPrintLink(string $url, string $text = 'Print', array $options = []): string
    {
        $options['icon'] = null;
        $options['class'] = $options['class'] ?? 'smart-print-link smart-print';

        return smartPrintButton($url, array_merge(['title' => $text], $options));
    }
}

// Register Blade directives
Blade::directive('smartPrintButton', function ($expression) {
    return "<?php echo smartPrintButton($expression); ?>";
});

Blade::directive('smartPrintLink', function ($expression) {
    return "<?php echo smartPrintLink($expression); ?>";
});
