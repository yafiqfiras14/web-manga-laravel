@php $manga = $manga ?? null; @endphp

<label for="title">Judul *</label>
<input type="text" id="title" name="title" value="{{ old('title', $manga->title ?? '') }}" required>

<label for="author">Author</label>
<input type="text" id="author" name="author" value="{{ old('author', $manga->author ?? '') }}">

<label for="synopsis">Sinopsis</label>
<textarea id="synopsis" name="synopsis" rows="5">{{ old('synopsis', $manga->synopsis ?? '') }}</textarea>

<label for="status">Status *</label>
<select id="status" name="status" required>
    @foreach (['ongoing' => 'Ongoing', 'completed' => 'Completed', 'hiatus' => 'Hiatus'] as $value => $label)
        <option value="{{ $value }}" @selected(old('status', $manga->status ?? 'ongoing') === $value)>{{ $label }}</option>
    @endforeach
</select>

<label for="cover">Cover (JPG/PNG/WEBP, maks 2MB)</label>
<input type="file" id="cover" name="cover" accept="image/*">
@if (!empty($manga->cover))
    <img src="{{ asset('storage/'.$manga->cover) }}" class="thumb" alt="Cover sekarang">
@endif
