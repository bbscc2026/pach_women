{{-- Info / policy page edited in Admin → Pages --}}
<x-policy :title="$page->title" :updated="$page->updated_at?->format('j F Y')">
    {!! $page->renderedContent() !!}

    @if ($page->slug === 'contact')
        <div class="grid gap-3 pt-4 sm:grid-cols-2" data-reveal>
            @foreach ([
                ['chat-bubble-left-right', 'WhatsApp / phone', config('shop.contact.phone'), 'https://wa.me/'.config('shop.contact.whatsapp')],
                ['envelope', 'Email', config('shop.contact.email'), 'mailto:'.config('shop.contact.email')],
                ['camera', 'Instagram', '@'.trim(parse_url((string) config('shop.contact.instagram'), PHP_URL_PATH), '/'), config('shop.contact.instagram')],
                ['map-pin', 'Store', config('shop.contact.store'), config('shop.contact.store_map')],
            ] as [$icon, $label, $value, $url])
                <a href="{{ $url }}" @if (! str_starts_with($url, 'mailto:')) target="_blank" rel="noopener" @endif
                   class="group flex items-start gap-4 bg-cream p-5 no-underline! transition hover:bg-sand">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-white transition group-hover:scale-105">
                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5 text-ink" />
                    </span>
                    <span>
                        <span class="label mb-0.5">{{ $label }}</span>
                        <span class="text-ink">{{ $value }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</x-policy>
