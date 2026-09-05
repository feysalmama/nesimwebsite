{{--
    What the five public forms show above their fields.

    The React forms replaced the whole form with a single success line when the
    fetch returned 200, and showed "Something went wrong — please try again." for
    everything else - a 400 for a missing phone number and a database outage read
    the same, and neither said which field had failed.

    These are ordinary POST forms following the redirect-back-with-errors pattern,
    so the outcome a visitor sees is specific: the flashed success sentence from
    the page's own translation namespace, or the list of validation messages with
    their input still in the fields below. The form stays on the page after a
    success rather than being replaced, so nobody has to reload to send a second
    enquiry.
--}}
@if (session('form_success'))
    <p class="rounded-2xl bg-canopy p-6 text-center text-sm font-medium text-forest">
        {{ session('form_success') }}
    </p>
@endif

@if ($errors->any())
    <div class="rounded-2xl border border-danger/25 bg-danger/5 p-4 text-sm text-danger">
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
