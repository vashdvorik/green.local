@foreach ($blocks ?? [] as $block)
    @php
        $type = $block['type'] ?? 'paragraph';
        $data = $block['data'] ?? [];
    @endphp
    @switch($type)
        @case('heading')
            @if (($data['level'] ?? 'h2') === 'h3')
                <h3>{{ $data['text'] ?? '' }}</h3>
            @else
                <h2>{{ $data['text'] ?? '' }}</h2>
            @endif
            @break
        @case('quote')
            <blockquote>{{ \App\Support\RichText::toText($data['text'] ?? '') }}</blockquote>
            @break
        @case('list')
            <ul>
                @foreach ($data['items'] ?? [] as $item)
                    <li>{{ is_array($item) ? ($item['text'] ?? '') : $item }}</li>
                @endforeach
            </ul>
            @break
        @case('image')
            <figure>
                <div class="dynamic-article__figure-media">
                    @include('partials.dynamic-image', ['path' => $data['path'] ?? null, 'alt' => '', 'seed' => $loop->index])
                </div>
            </figure>
            @break
        @case('image_text_photo_left')
        @case('image_text_text_left')
            @php
                $imageFirst = $type === 'image_text_photo_left';
            @endphp
            <div class="dynamic-article__image-text {{ $imageFirst ? 'dynamic-article__image-text--photo-left' : 'dynamic-article__image-text--text-left' }}">
                @if ($imageFirst)
                    <figure>
                        <div class="dynamic-article__figure-media dynamic-article__image-text-media">
                            @include('partials.dynamic-image', ['path' => $data['path'] ?? null, 'alt' => '', 'seed' => $loop->index])
                        </div>
                    </figure>
                    <div class="dynamic-article__image-text-copy rich-text">{!! \App\Support\RichText::toHtml($data['text'] ?? '') !!}</div>
                @else
                    <div class="dynamic-article__image-text-copy rich-text">{!! \App\Support\RichText::toHtml($data['text'] ?? '') !!}</div>
                    <figure>
                        <div class="dynamic-article__figure-media dynamic-article__image-text-media">
                            @include('partials.dynamic-image', ['path' => $data['path'] ?? null, 'alt' => '', 'seed' => $loop->index])
                        </div>
                    </figure>
                @endif
            </div>
            @break
        @case('gallery')
        @case('gallery_2')
        @case('gallery_3')
        @case('gallery_4')
            @php
                $gallerySize = match ($type) {
                    'gallery_2' => 2,
                    'gallery_4' => 4,
                    default => 3,
                };
            @endphp
            <div class="dynamic-article__gallery dynamic-article__gallery--{{ $gallerySize }}">
                @foreach ($data['images'] ?? [] as $image)
                    <figure>
                        <div class="dynamic-article__gallery-media">
                            @include('partials.dynamic-image', ['path' => $image['path'] ?? null, 'alt' => '', 'seed' => $loop->parent->index . '-' . $loop->index])
                        </div>
                    </figure>
                @endforeach
            </div>
            @break
        @case('video')
            @php
                $videoUrl = trim((string) ($data['url'] ?? ''));
                $videoParts = parse_url($videoUrl);
                $videoHost = strtolower((string) ($videoParts['host'] ?? ''));
                $videoEmbed = null;
                if (in_array($videoHost, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true) && filled($videoParts['query'] ?? null)) {
                    parse_str($videoParts['query'], $videoQuery);
                    $videoEmbed = filled($videoQuery['v'] ?? null) ? 'https://www.youtube-nocookie.com/embed/'.rawurlencode($videoQuery['v']) : null;
                } elseif ($videoHost === 'youtu.be') {
                    $videoEmbed = 'https://www.youtube-nocookie.com/embed/'.rawurlencode(trim((string) ($videoParts['path'] ?? ''), '/'));
                } elseif ($videoHost === 'vimeo.com') {
                    $videoEmbed = 'https://player.vimeo.com/video/'.rawurlencode(trim((string) ($videoParts['path'] ?? ''), '/'));
                } elseif (in_array($videoHost, ['www.youtube-nocookie.com', 'player.vimeo.com'], true) && str_contains((string) ($videoParts['path'] ?? ''), '/embed/')) {
                    $videoEmbed = $videoUrl;
                }
            @endphp
            @if (filled($videoEmbed))
                <figure class="dynamic-article__embed">
                    <div class="dynamic-article__embed-media">
                        <iframe src="{{ $videoEmbed }}" title="{{ $data['caption'] ?? 'Видео' }}" loading="lazy" allowfullscreen></iframe>
                    </div>
                    @if (filled($data['caption'] ?? null))
                        <figcaption>{{ $data['caption'] }}</figcaption>
                    @endif
                </figure>
            @endif
            @break
        @default
            <div class="rich-text">{!! \App\Support\RichText::toHtml($data['text'] ?? '') !!}</div>
    @endswitch
@endforeach
