@props(['label', 'name', 'options' => [], 'placeholder' => null, 'required' => false])

{{--
    The <select> the donate and membership forms inlined. `options` is a
    value => label map, so a caller can pass a literal array (the three payment
    methods) or MembershipCategory::pluck() (the tiers, whose names are
    three-language columns the controller has already resolved).

    `placeholder` renders an empty-valued first option, which is what lets the
    membership form start on "Select an option" instead of silently preselecting
    a tier the visitor never chose.
--}}
<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-ink/80">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}"
            @if ($required) required @endif
            @class([
                'w-full rounded-xl border bg-white px-4 py-2.5 text-sm outline-none focus:border-sun',
                'border-danger' => $errors->has($name),
                'border-leaf/25' => ! $errors->has($name),
            ])>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) old($name) === (string) $value)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
