@props(['text' => '', 'margin' => 'mt-4'])

{{--
    The article body block the five detail pages share.

    `prose prose-sm` comes straight off the React pages and does nothing in
    either build: neither the archived tailwind.config.ts (plugins: []) nor
    resources/css/app.css loads @tailwindcss/typography, so there are no .prose
    rules for those classes to match. What actually makes a body readable is
    whitespace-pre-line, which turns the newlines an editor typed into the CMS
    textarea into line breaks.

    The classes are kept so the markup matches the pages it was ported from, and
    this note is here so the next reader does not spend an afternoon working out
    why the typography plugin appears to be broken.

    `margin` rather than a merged class attribute: the React pages used mt-4 on
    four of the five and mt-8 on the news detail, and two competing margin
    utilities in one class list resolve by stylesheet order, not by which was
    written last.
--}}
<div class="prose prose-sm {{ $margin }} max-w-none whitespace-pre-line text-[15px] leading-relaxed text-ink/85">
    {{ $text }}
</div>
