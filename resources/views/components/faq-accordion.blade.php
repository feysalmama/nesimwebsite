@props(['items' => []])

{{--
    FaqAccordion from components/FaqAccordion.tsx, which held the open item in
    React state and started with the first one open.

    Native <details> rather than a button plus a class toggle. The disclosure is
    what the element is for, it works with JavaScript disabled, and a screen
    reader announces it as expandable without an aria-expanded attribute that
    someone has to remember to keep in step. `open` on the first item reproduces
    the state the React version mounted in.

    One difference worth naming: the React accordion closed whichever item was
    open when another was clicked, and <details> leaves each one independent.
    Nothing in the design depends on exactly one being open, and a visitor who
    wants to compare two answers can now do it.
--}}
<div class="divide-y divide-leaf/15 rounded-2xl border border-leaf/15 bg-white shadow-sm">
    @foreach ($items as $item)
        <details class="group" @if ($loop->first) open @endif>
            <summary class="flex w-full cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left [&::-webkit-details-marker]:hidden">
                <span class="font-display text-[15px] font-semibold text-forest">{{ $item['question'] }}</span>
                <span class="shrink-0 text-lg text-sun transition-transform group-open:rotate-45" aria-hidden="true">+</span>
            </summary>
            <div class="px-5 pb-4 text-sm leading-relaxed text-stone">{{ $item['answer'] }}</div>
        </details>
    @endforeach
</div>
