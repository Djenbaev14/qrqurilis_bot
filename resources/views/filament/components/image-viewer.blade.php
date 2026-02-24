@php
    $filePath = $getState();
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $videoExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'webm'];
    $isVideo = in_array(strtolower($extension), $videoExtensions);
@endphp

<div x-data="{}">
    @if($isVideo)
        {{-- Video preview --}}
        <div class="relative flex items-center justify-center bg-black rounded-lg overflow-hidden h-40 cursor-pointer"
             @click="$dispatch('open-modal', { id: 'preview-media-{{ Str::slug($filePath) }}' })">
            <video class="w-full h-full object-cover opacity-60">
                <source src="{{ asset('storage/' . $filePath) }}">
            </video>
            <div class="absolute inset-0 flex items-center justify-center">
                <x-heroicon-o-play-circle class="w-8 h-8 text-white" />
            </div>
        </div>
    @else
        {{-- Image preview --}}
        <img src="{{ asset('storage/' . $filePath) }}" 
             class="rounded-lg shadow-md cursor-zoom-in w-full h-40 object-cover hover:scale-105 transition-transform"
             @click="$dispatch('open-modal', { id: 'preview-media-{{ Str::slug($filePath) }}' })">
    @endif

    {{-- Kattalashtirib ko'rish uchun Modal --}}
    <x-filament::modal id="preview-media-{{ Str::slug($filePath) }}" width="4xl" display-classes="block">
        <div class="flex justify-center p-2">
            @if($isVideo)
                <video controls autoplay class="w-full max-h-[80vh]">
                    <source src="{{ asset('storage/' . $filePath) }}">
                    Sizning brauzeringiz videoni qo'llab-quvvatlamaydi.
                </video>
            @else
                <img src="{{ asset('storage/' . $filePath) }}" class="w-full h-auto max-h-[80vh] object-contain">
            @endif
        </div>
    </x-filament::modal>
</div>