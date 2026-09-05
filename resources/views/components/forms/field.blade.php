@props(['label', 'name', 'type' => 'text', 'required' => false, 'value' => null])

{{--
    Field, defined at the foot of components/forms/ContactForm.tsx and imported by
    the donate, volunteer and register forms - one component, five pages, which
    is why it is worth having here too.

    Two things the React version did not do. Its <label> had no htmlFor, so
    clicking a label did not focus its input and a screen reader announced the
    field as unlabelled; the for/id pair fixes both. And it showed a single
    "Something went wrong" for any failure, so a visitor who mistyped one field
    out of six had no way to tell which. These are plain POST forms, so Laravel
    redirects back with the input flashed: old() puts what they typed back in the
    box, and $errors marks the field that failed and says why under it.
--}}
<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-ink/80">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}"
           @if ($required) required @endif
           @class([
               'w-full rounded-xl border bg-white px-4 py-2.5 text-sm outline-none focus:border-sun',
               'border-danger' => $errors->has($name),
               'border-leaf/25' => ! $errors->has($name),
           ])>
    @error($name)
        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
