@php($active = $active ?? '')
<nav class="media-switcher" aria-label="Media">
    <a href="{{ route('media.photos') }}" class="{{ $active === 'photos' ? 'is-active' : '' }}" @if($active === 'photos') aria-current="page" @endif>
        <span class="locale-copy locale-copy--ru">Фото</span>
        <span class="locale-copy locale-copy--ro">Foto</span>
        <span class="locale-copy locale-copy--en">Photos</span>
    </a>
    <a href="{{ route('media.videos') }}" class="{{ $active === 'videos' ? 'is-active' : '' }}" @if($active === 'videos') aria-current="page" @endif>
        <span class="locale-copy locale-copy--ru">Видео</span>
        <span class="locale-copy locale-copy--ro">Video</span>
        <span class="locale-copy locale-copy--en">Videos</span>
    </a>
    <a href="{{ route('media.catalogues') }}" class="{{ $active === 'catalogues' ? 'is-active' : '' }}" @if($active === 'catalogues') aria-current="page" @endif>
        <span class="locale-copy locale-copy--ru">Каталоги</span>
        <span class="locale-copy locale-copy--ro">Cataloage</span>
        <span class="locale-copy locale-copy--en">Catalogues</span>
    </a>
</nav>
