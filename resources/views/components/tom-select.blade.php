@props([
    'name',
    'id' => null,
    'placeholder' => 'Pilih opsi...',
    'multiple' => false,
    'searchable' => true,
    'options' => [],
    'selected' => null,
    'maxItems' => null,
])

@php
    $selectId = $id ?? 'ts_' . str_replace(['[', ']', '.'], '_', $name) . '_' . substr(md5(uniqid('', true)), 0, 6);
    $isMultiple = (bool) $multiple;
    $isSearchable = (bool) $searchable;
@endphp

<div 
    wire:ignore
    x-data="{
        ts: null,
        init() {
            this.$nextTick(() => {
                if (window.initCbtTomSelect) {
                    this.ts = window.initCbtTomSelect(this.$refs.selectElement, {
                        multiple: {{ $isMultiple ? 'true' : 'false' }},
                        maxItems: {{ $isMultiple ? ($maxItems ? (int)$maxItems : 'null') : '1' }},
                        placeholder: @js($placeholder),
                        searchable: {{ $isSearchable ? 'true' : 'false' }}
                    });
                }
            });
        },
        destroy() {
            if (this.ts) {
                this.ts.destroy();
                this.ts = null;
            }
        }
    }"
    x-on:unmount="destroy()"
    class="w-full relative"
>
    <select 
        x-ref="selectElement"
        name="{{ $name }}" 
        id="{{ $selectId }}"
        {{ $isMultiple ? 'multiple' : '' }}
        {{ $attributes->except(['class', 'placeholder', 'multiple', 'searchable', 'options', 'selected', 'maxItems']) }}
        class="{{ $attributes->get('class', 'w-full') }}"
    >
        @if(!$isMultiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if(!empty($options))
            @foreach($options as $val => $label)
                @php
                    $optVal = is_array($label) ? ($label['id'] ?? $label['value'] ?? $val) : (is_object($label) ? ($label->id ?? $label->value ?? $val) : $val);
                    $optLabel = is_array($label) ? ($label['name'] ?? $label['nama'] ?? $label['label'] ?? $optVal) : (is_object($label) ? ($label->name ?? $label->nama ?? $label->label ?? $optVal) : $label);
                    $isSelected = false;
                    if (is_array($selected)) {
                        $isSelected = in_array($optVal, $selected);
                    } elseif ($selected !== null) {
                        $isSelected = ((string)$selected === (string)$optVal);
                    }
                @endphp
                <option value="{{ $optVal }}" {{ $isSelected ? 'selected' : '' }}>{{ $optLabel }}</option>
            @endforeach
        @endif

        {{ $slot }}
    </select>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/tom-select/tom-select.default.min.css') }}">
        <style>
            /* Custom Tailwind & Dark Mode integration for Tom Select */
            .ts-wrapper {
                width: 100% !important;
                font-family: inherit !important;
            }
            .ts-wrapper .ts-control {
                border-radius: 0.75rem !important; /* rounded-xl */
                border-width: 1px !important;
                border-color: #e2e8f0 !important; /* slate-200 */
                background-color: #f8fafc !important; /* slate-50 */
                color: #0f172a !important; /* slate-900 */
                padding: 0.45rem 0.75rem !important;
                font-size: 0.75rem !important; /* text-xs */
                line-height: 1.25rem !important;
                min-height: 38px !important;
                display: flex !important;
                align-items: center !important;
                flex-wrap: wrap !important;
                gap: 0.35rem !important;
                box-shadow: none !important;
                transition: all 0.15s ease-in-out !important;
            }
            .dark .ts-wrapper .ts-control {
                background-color: #020617 !important; /* slate-950 */
                border-color: #1e293b !important; /* slate-800 */
                color: #f8fafc !important; /* slate-50 */
            }
            .ts-wrapper.focus .ts-control {
                border-color: #6366f1 !important; /* brand-500 */
                box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
                background-color: #ffffff !important;
            }
            .dark .ts-wrapper.focus .ts-control {
                border-color: #818cf8 !important; /* brand-400 */
                box-shadow: 0 0 0 2px rgba(129, 140, 248, 0.25) !important;
                background-color: #0f172a !important;
            }
            .ts-wrapper .ts-control input {
                color: inherit !important;
                font-size: 0.75rem !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .ts-wrapper .ts-control input::placeholder {
                color: #94a3b8 !important; /* slate-400 */
            }
            .dark .ts-wrapper .ts-control input::placeholder {
                color: #64748b !important; /* slate-500 */
            }

            /* Dropdown Menu Container */
            .ts-dropdown {
                border-radius: 0.75rem !important; /* rounded-xl */
                border: 1px solid #e2e8f0 !important; /* slate-200 */
                background-color: #ffffff !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
                margin-top: 0.25rem !important;
                padding: 0.35rem !important;
                z-index: 9999 !important;
                font-size: 0.75rem !important; /* text-xs */
                overflow: hidden !important;
            }
            .dark .ts-dropdown {
                background-color: #0f172a !important; /* slate-900 */
                border-color: #334155 !important; /* slate-700 */
                color: #f8fafc !important;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
            }
            .ts-dropdown .option {
                padding: 0.5rem 0.75rem !important;
                border-radius: 0.5rem !important;
                cursor: pointer !important;
                color: #334155 !important;
                transition: background-color 0.1s ease !important;
            }
            .dark .ts-dropdown .option {
                color: #cbd5e1 !important;
            }
            .ts-dropdown .option:hover,
            .ts-dropdown .option.active {
                background-color: #eef2ff !important; /* brand-50 */
                color: #4f46e5 !important; /* brand-600 */
                font-weight: 600 !important;
            }
            .dark .ts-dropdown .option:hover,
            .dark .ts-dropdown .option.active {
                background-color: rgba(99, 102, 241, 0.2) !important;
                color: #c7d2fe !important;
            }
            .ts-dropdown .option.selected {
                background-color: #f1f5f9 !important;
                color: #64748b !important;
            }
            .dark .ts-dropdown .option.selected {
                background-color: #1e293b !important;
                color: #94a3b8 !important;
            }

            /* Multiple Tag Badge Pill */
            .ts-wrapper.multi .ts-control > div {
                background: #eef2ff !important; /* brand-50 */
                color: #4338ca !important; /* brand-700 */
                border: 1px solid #c7d2fe !important; /* brand-200 */
                border-radius: 9999px !important; /* pill */
                padding: 0.15rem 0.55rem !important;
                font-size: 0.7rem !important;
                font-weight: 700 !important;
                display: inline-flex !important;
                align-items: center !important;
                gap: 0.35rem !important;
                margin: 0.1rem !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
            }
            .dark .ts-wrapper.multi .ts-control > div {
                background: rgba(99, 102, 241, 0.25) !important;
                color: #c7d2fe !important;
                border-color: rgba(99, 102, 241, 0.4) !important;
            }
            /* Plugin remove_button styling */
            .ts-wrapper.plugin-remove_button .item .remove {
                border-left: 1px solid rgba(67, 56, 202, 0.25) !important;
                margin-left: 0.25rem !important;
                padding-left: 0.35rem !important;
                color: #4338ca !important;
                text-decoration: none !important;
                font-weight: bold !important;
                line-height: 1 !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: 0 9999px 9999px 0 !important;
                opacity: 0.8 !important;
                transition: all 0.15s ease !important;
            }
            .dark .ts-wrapper.plugin-remove_button .item .remove {
                border-left-color: rgba(199, 210, 254, 0.3) !important;
                color: #c7d2fe !important;
            }
            .ts-wrapper.plugin-remove_button .item .remove:hover {
                background: rgba(239, 68, 68, 0.2) !important;
                color: #ef4444 !important;
                opacity: 1 !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('vendor/tom-select/tom-select.complete.min.js') }}"></script>
        <script>
            if (!window.initCbtTomSelect) {
                window.initCbtTomSelect = function(el, config) {
                    if (!el || typeof TomSelect === 'undefined') return null;
                    if (el.tomselect) {
                        el.tomselect.destroy();
                    }
                    const plugins = [];
                    if (config.multiple) {
                        plugins.push('remove_button');
                    }
                    return new TomSelect(el, {
                        plugins: plugins,
                        create: false,
                        maxItems: config.multiple ? (config.maxItems ? config.maxItems : null) : 1,
                        searchField: ['text'],
                        placeholder: config.placeholder || 'Pilih opsi...',
                        allowEmptyOption: true,
                        closeAfterSelect: !config.multiple,
                        hidePlaceholder: false,
                        controlInput: config.searchable ? null : false,
                        render: {
                            no_results: function(data, escape) {
                                return '<div class="no-results p-2.5 text-xs text-slate-400 dark:text-slate-500 text-center font-medium">Tidak ada opsi yang sesuai</div>';
                            }
                        },
                        onChange: function(value) {
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                };
            }
        </script>
    @endpush
@endonce
