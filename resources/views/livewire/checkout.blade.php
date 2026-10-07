@php
    $methodInfo = [
        'cod' => ['banknotes', 'Pay in cash when your order arrives.'],
        'razorpay' => ['credit-card', 'UPI, cards, netbanking and wallets via Razorpay.'],
    ];
    $buttonLabel = $paymentMethod === 'razorpay' ? 'Pay '.inr($total) : 'Place order · '.inr($total);
@endphp

<div class="container-shop pt-6 pb-32 sm:pt-10 lg:pb-10">
    {{-- Steps --}}
    <ol class="flex items-center justify-center gap-2 text-[11px] tracking-wider text-neutral-500 uppercase sm:justify-start">
        <li><a href="{{ route('cart') }}" wire:navigate class="flex items-center gap-1.5 hover:text-ink"><x-heroicon-s-check-circle class="size-4 text-ink" /> Cart</a></li>
        <li class="h-px w-6 bg-sand"></li>
        <li class="flex items-center gap-1.5 text-ink"><span class="flex size-4 items-center justify-center rounded-full bg-ink text-[9px] text-white">2</span> Details</li>
        <li class="h-px w-6 bg-sand"></li>
        <li class="flex items-center gap-1.5"><span class="flex size-4 items-center justify-center rounded-full border border-neutral-400 text-[9px]">3</span> Done</li>
    </ol>

    <h1 class="mt-5 text-4xl font-semibold">Checkout</h1>

    @if ($paymentNotice === 'cancelled')
        <p class="mt-5 flex items-start gap-2 bg-cream px-4 py-3 text-sm"><x-heroicon-o-information-circle class="size-5 shrink-0" /> Payment was cancelled. Your cart is saved, so you can try again or choose another payment method.</p>
    @endif

    @error('cart')
        <p class="mt-5 flex items-start gap-2 bg-red-50 px-4 py-3 text-sm text-red-800"><x-heroicon-o-exclamation-circle class="size-5 shrink-0" /> {{ $message }}</p>
    @enderror

    <form id="checkout-form" wire:submit="placeOrder" class="mt-6 grid gap-8 lg:mt-8 lg:grid-cols-3 lg:gap-10">
        {{-- Order summary: collapsible on phones, sticky column on desktop --}}
        <aside x-data="{ open: false }" class="h-fit bg-cream lg:sticky lg:top-24 lg:order-2 lg:p-6">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between px-4 py-4 text-sm lg:hidden" :aria-expanded="open">
                <span class="flex items-center gap-2">
                    <x-heroicon-o-shopping-bag class="size-5" />
                    <span x-text="open ? 'Hide order summary' : 'Show order summary'">Show order summary</span>
                    <span wire:ignore.self class="transition" :class="open && 'rotate-180'"><x-heroicon-o-chevron-down class="size-4" /></span>
                </span>
                <span class="font-medium">{{ inr($total) }}</span>
            </button>

            <div wire:ignore.self :class="open ? 'block' : 'hidden'" class="border-t border-sand px-4 pb-5 lg:block! lg:border-0 lg:p-0">
                <h2 class="hidden font-serif text-2xl font-semibold lg:block">Your order</h2>
                <ul class="mt-5 space-y-4 lg:mt-6">
                    @foreach ($items as $item)
                        <li wire:key="co-{{ $item->key }}" class="flex gap-3 text-sm">
                            <div class="relative w-14 shrink-0">
                                <div class="aspect-[3/4] overflow-hidden bg-sand">
                                    @if ($item->product->thumbnailUrl())
                                        <img src="{{ $item->product->thumbnailUrl() }}" alt="" class="size-full object-cover">
                                    @endif
                                </div>
                                <span class="absolute -top-2 -right-2 flex size-5 items-center justify-center rounded-full bg-ink text-[10px] text-white">{{ $item->quantity }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="leading-snug">{{ $item->product->name }}</p>
                                @if ($item->size)<p class="text-xs text-neutral-500">Size: {{ $item->size }}</p>@endif
                            </div>
                            <p>{{ inr($item->total) }}</p>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 border-t border-sand pt-6">
                    <x-order-summary :subtotal="$subtotal" :shipping="$shipping" :total="$total" />
                </div>
            </div>

            <div class="hidden lg:block">
                <button type="submit" wire:loading.attr="disabled" wire:target="placeOrder" @disabled(empty($methods)) class="btn-dark mt-6 w-full">
                    <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-2"><x-heroicon-o-lock-closed class="size-4" /> {{ $buttonLabel }}</span>
                    <span wire:loading wire:target="placeOrder" class="size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                </button>
                <p class="mt-4 text-center text-xs text-neutral-500">
                    By placing this order you agree to our <a href="{{ route('page', 'terms') }}" class="underline">terms</a> and
                    <a href="{{ route('page', 'returns') }}" class="underline">no return / refund policy</a>.
                </p>
            </div>
        </aside>

        <div class="space-y-10 lg:order-1 lg:col-span-2">
            <section>
                <h2 class="flex items-center gap-2 font-serif text-2xl font-semibold"><x-heroicon-o-map-pin class="size-6" /> Delivery address</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">Full name</label>
                        <input id="name" wire:model="name" autocomplete="name" enterkeyhint="next" class="input">
                        @error('name') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="phone" class="label">Mobile number</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-base text-neutral-500 sm:text-sm">+91</span>
                            <input id="phone" type="tel" inputmode="numeric" wire:model="phone" autocomplete="tel-national" maxlength="14" placeholder="10-digit mobile" enterkeyhint="next" class="input pl-12">
                        </div>
                        @error('phone') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" type="email" inputmode="email" wire:model="email" autocomplete="email" enterkeyhint="next" class="input">
                        @error('email') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="line1" class="label">House / flat, street</label>
                        <input id="line1" wire:model="line1" autocomplete="address-line1" enterkeyhint="next" class="input">
                        @error('line1') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="line2" class="label">Area, landmark (optional)</label>
                        <input id="line2" wire:model="line2" autocomplete="address-line2" enterkeyhint="next" class="input">
                    </div>

                    {{-- PIN code fills in city and state when the lookup service answers --}}
                    <div x-data="{
                            looking: false,
                            async lookup(pin) {
                                if (! /^\d{6}$/.test(pin)) return;
                                this.looking = true;
                                try {
                                    const controller = new AbortController();
                                    setTimeout(() => controller.abort(), 4000);
                                    const res = await fetch('https://api.postalpincode.in/pincode/' + pin, { signal: controller.signal });
                                    const office = (await res.json())?.[0]?.PostOffice?.[0];
                                    if (office) {
                                        if (! $wire.city) $wire.city = office.District;
                                        if (@js($states).includes(office.State)) $wire.state = office.State;
                                    }
                                } catch (e) {} finally { this.looking = false; }
                            },
                         }">
                        <label for="pincode" class="label">PIN code</label>
                        <div class="relative">
                            <input id="pincode" wire:model="pincode" inputmode="numeric" maxlength="6" autocomplete="postal-code" enterkeyhint="next"
                                   @input.debounce.300ms="lookup($event.target.value)" class="input">
                            <span wire:ignore.self x-show="looking" x-cloak class="absolute top-1/2 right-3 size-4 -translate-y-1/2 animate-spin rounded-full border-2 border-sand border-t-ink"></span>
                        </div>
                        @error('pincode') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="city" class="label">City / town</label>
                        <input id="city" wire:model="city" autocomplete="address-level2" enterkeyhint="next" class="input">
                        @error('city') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="state" class="label">State</label>
                        <select id="state" wire:model="state" autocomplete="address-level1" class="input">
                            @foreach ($states as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('state') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Order notes (optional)</label>
                        <textarea id="notes" wire:model="notes" rows="2" placeholder="Delivery instructions, gift message…" class="input"></textarea>
                    </div>
                    <label class="flex min-h-11 items-center gap-3 text-sm sm:col-span-2">
                        <input type="checkbox" wire:model="saveAddress" class="size-5 accent-ink">
                        Save this address to my account
                    </label>
                </div>
            </section>

            <section>
                <h2 class="flex items-center gap-2 font-serif text-2xl font-semibold"><x-heroicon-o-wallet class="size-6" /> Payment</h2>
                <div class="mt-5 space-y-3">
                    @forelse ($methods as $key => $label)
                        @php([$icon, $hint] = $methodInfo[$key] ?? ['credit-card', ''])
                        <label @class([
                            'flex cursor-pointer items-center gap-4 border px-4 py-4 transition',
                            'border-ink bg-cream/60' => $paymentMethod === $key,
                            'border-sand hover:border-neutral-400' => $paymentMethod !== $key,
                        ])>
                            <input type="radio" wire:model.live="paymentMethod" value="{{ $key }}" class="size-5 shrink-0 accent-ink">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white">
                                <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium">{{ $label }}</span>
                                <span class="block text-xs text-neutral-500">{{ $hint }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-sale">No payment method is available. Please contact us on WhatsApp to order.</p>
                    @endforelse
                    @error('paymentMethod') <p class="error">{{ $message }}</p> @enderror
                </div>
                <p class="mt-4 flex items-center gap-2 text-xs text-neutral-500"><x-heroicon-o-shield-check class="size-4" /> Your details are encrypted and never shared.</p>
            </section>
        </div>
    </form>

    {{-- Sticky pay bar on phones --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-sand bg-white px-4 pt-3 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] pb-safe lg:hidden">
        <button type="submit" form="checkout-form" wire:loading.attr="disabled" wire:target="placeOrder" @disabled(empty($methods)) class="btn-dark w-full">
            <span wire:loading.remove wire:target="placeOrder" class="flex items-center gap-2"><x-heroicon-o-lock-closed class="size-4" /> {{ $buttonLabel }}</span>
            <span wire:loading wire:target="placeOrder" class="size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
        </button>
        <p class="mt-2 text-center text-[11px] text-neutral-500">
            By ordering you agree to our <a href="{{ route('page', 'terms') }}" class="underline">terms</a> &amp; <a href="{{ route('page', 'returns') }}" class="underline">no-return policy</a>.
        </p>
    </div>
</div>
