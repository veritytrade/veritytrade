<x-app-layout>
    <div class="min-h-screen">
        @if($hero && $hero->hero_visible && ($hero->hero_headline || $hero->hero_image_path))
            @php
                $hasText = $hero->hero_headline || $hero->hero_subheadline || $hero->hero_cta_text;
                $hasImage = (bool) $hero->hero_image_path;
                $useSplit = $hasText && $hasImage;
            @endphp
            <section class="bg-gradient-to-br from-slate-50 via-white to-green-50/30 border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
                    <div class="flex flex-col {{ $useSplit ? 'lg:flex-row lg:items-center lg:justify-between lg:gap-12 xl:gap-16' : '' }} {{ $hasImage && !$hasText ? 'items-center' : '' }}">
                        @if($hasText)
                            <div class="flex-1 text-center {{ $useSplit ? 'lg:text-left order-2 lg:order-1' : '' }}">
                                @if($hero->hero_headline)
                                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 leading-tight">
                                        {{ $hero->hero_headline }}
                                    </h1>
                                @endif
                                @if($hero->hero_subheadline)
                                    <p class="mt-3 sm:mt-4 text-base sm:text-lg text-gray-600 max-w-xl mx-auto {{ $useSplit ? 'lg:mx-0' : '' }}">{{ $hero->hero_subheadline }}</p>
                                @endif
                                @if($hero->hero_cta_text)
                                    <a href="{{ $hero->hero_cta_url ?: '#hot-deals' }}" class="inline-flex items-center justify-center mt-6 sm:mt-8 px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-base sm:text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                                        {{ $hero->hero_cta_text }}
                                    </a>
                                @endif
                            </div>
                        @endif
                        @if($hasImage)
                            <div class="flex-shrink-0 flex justify-center {{ $useSplit ? 'lg:justify-end order-1 lg:order-2 lg:w-[45%] xl:w-[50%]' : 'w-full' }}">
                                <img src="{{ $hero->hero_image_url }}" alt="" class="w-full {{ $useSplit ? 'max-w-sm sm:max-w-md lg:max-w-full max-h-[280px] sm:max-h-[320px] lg:max-h-[360px] xl:max-h-[420px]' : 'max-w-md lg:max-w-2xl xl:max-w-3xl max-h-[320px] lg:max-h-[400px]' }} object-contain object-center drop-shadow-2xl" loading="eager">
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <x-hot-deals-section :deals="$deals" />

        <div class="fixed bottom-4 right-4 md:hidden z-50">
            @php $waNumber = preg_replace('/\D/', '', (string) site_setting('whatsapp_number', site_setting('whatsapp_business_number', '2347084117779'))); @endphp
            <a href="https://wa.me/{{ $waNumber ?: '2347084117779' }}" class="flex items-center justify-center w-14 h-14 text-white bg-green-600 hover:bg-green-700 rounded-full shadow-lg focus:ring-4 focus:ring-green-300 transition" aria-label="WhatsApp">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.459-2.39-1.485-.883-.793-1.48-1.76-1.653-2.057-.173-.298-.022-.458.126-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.199.05-.372-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.372-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.195 1.871.118.571-.086 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.744.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/>
                </svg>
            </a>
        </div>

        <style>
            .scrollbar-hide::-webkit-scrollbar { display: none; }
            .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        </style>

    </div>
</x-app-layout>
