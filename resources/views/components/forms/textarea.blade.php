@props(['label', 'name', 'rows' => 4, 'required' => false])

{{--
    The textarea the React forms inlined rather than folding into Field, because
    Field renders an <input>. Same labelling and same error handling as
    forms/field.blade.php; the two are deliberately indistinguishable to a
    visitor.
--}}
<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-ink/80">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              @if ($required) required @endif
              @class([
                  'w-full rounded-xl border bg-white px-4 py-2.5 text-sm outline-none focus:border-sun',
                  'border-danger' => $errors->has($name),
                  'border-leaf/25' => ! $errors->has($name),
              ])>{{ old($name) }}</textarea>
    @error($name)
        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
