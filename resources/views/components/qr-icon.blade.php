@props(['type' => 'none'])

@php
    $svgAttrs = $attributes->merge(['class' => 'h-6 w-6', 'fill' => 'none', 'viewBox' => '0 0 24 24']);
@endphp

@switch($type)
    @case('snapchat')
        <svg {{ $svgAttrs }}><path d="M12 3c2.5 0 4 1.8 4 4.2 0 1 .1 1.9.3 2.4.3.6 1.2.5 1.8.8.3.2.4.6.1.9-.4.5-1.6.7-1.8 1.1-.2.4.4 1.4 1.6 2.4 1 .8 2 .9 2 1.4 0 .6-1.3.7-1.7 1-.2.2 0 .9-.5 1.1-.4.2-1.2-.2-2 0-.7.2-1.2 1.3-2.3 1.6-.6.2-1.2.1-1.8-.1-.8-.3-1.3-1-2.3-1.3-1-.3-2 .3-2.4.1-.5-.2-.3-.9-.5-1.1-.4-.3-1.7-.4-1.7-1 0-.5 1-.6 2-1.4 1.2-1 1.8-2 1.6-2.4-.2-.4-1.4-.6-1.8-1.1-.3-.3-.2-.7.1-.9.6-.3 1.5-.2 1.8-.8.2-.5.3-1.4.3-2.4C8 4.8 9.5 3 12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
        @break
    @case('instagram')
        <svg {{ $svgAttrs }}><rect x="3.5" y="3.5" width="17" height="17" rx="5" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.6"/><circle cx="17" cy="7" r="1.2" fill="currentColor"/></svg>
        @break
    @case('linkedin')
        <svg {{ $svgAttrs }}><rect x="3.5" y="3.5" width="17" height="17" rx="3" stroke="currentColor" stroke-width="1.6"/><path d="M7.5 10v6.5M7.5 7.2v.01M11 16.5V13c0-1.4 1-2.3 2.2-2.3S15.5 11.6 15.5 13v3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        @break
    @case('url')
        <svg {{ $svgAttrs }}><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 12h17M12 3.5c2.5 2.3 2.5 14.7 0 17M12 3.5c-2.5 2.3-2.5 14.7 0 17" stroke="currentColor" stroke-width="1.6"/></svg>
        @break
    @case('text')
        <svg {{ $svgAttrs }}><path d="M5 6h14M5 6V5m14 1V5M12 6v13M9 19h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        @break
    @default
        <svg {{ $svgAttrs }}><rect x="4" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="14" y="4" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="4" y="14" width="6" height="6" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M14 14h3v3M20 14v.01M14 20h.01M20 20h.01M17 20v.01M20 17h.01" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
@endswitch
