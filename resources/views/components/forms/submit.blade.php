@props(['label'])

{{--
    The submit button all five public forms shared, styling and all.

    `disabled:opacity-60` is here for the form's data-submit-guard, which
    resources/js/app.js uses to disable the button on submit so a slow connection
    cannot produce two identical enquiries. The React forms did the same thing
    from their own loading state and swapped the label to "…"; a plain POST form
    has no state to swap from, and a button that greys out under the cursor says
    the same thing.
--}}
<button type="submit"
        class="w-full rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white transition hover:bg-sunlight disabled:opacity-60 sm:w-auto">
    {{ $label }}
</button>
