<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * lib/slug.ts, ported.
 *
 * That file was dead code in the Next.js app — nothing imported it — which is
 * why services showed a field labelled "Slug (auto-generated)" that generated
 * nothing, and why projects and news posts had no slug field at all and were
 * saved with slug = NULL. lib/content.ts resolves all four through
 * findUnique({ where: { slug } }), so every project and news detail page created
 * through the CMS was unreachable.
 *
 * The admin now calls this whenever a spec declares slugBase(), which makes the
 * label true and closes that hole.
 */
class Slug
{
    /**
     * The slugify() half. Str::slug() lowercases, strips everything that is not
     * a letter, a digit or a separator, and joins the words with "-", which is
     * what the JavaScript did with its three replace() calls.
     *
     * One deliberate difference: Str::slug() transliterates to ASCII, so a title
     * written only in Amharic or Afaan Oromoo yields "" and falls through to
     * "untitled" below. The JavaScript's /[^\w\s-]/ stripped non-ASCII too, so
     * the outcome is the same — but transliteration means an accented Latin
     * title keeps its letters instead of losing them.
     *
     * The 80-character cap is the original's .slice(0, 80), kept so a long title
     * cannot produce a slug long enough to matter against the 191-character
     * column once the uniqueness counter is appended.
     */
    public static function make(string $text): string
    {
        return Str::limit(Str::slug($text), 80, '');
    }

    /**
     * The uniqueSlug() half: the base slug if it is free, otherwise the base
     * with the lowest unused "-N" appended.
     *
     * $currentId excludes the row being edited, so reopening a project and
     * saving it does not bump its own slug to "my-project-1".
     *
     * One query rather than the original's fetch-everything-then-filter: only
     * the slugs that could collide are read, and only their slug column.
     *
     * @param  class-string<Model>  $model
     */
    public static function unique(string $base, string $model, ?string $currentId = null): string
    {
        $slug = self::make($base);

        if ($slug === '') {
            $slug = 'untitled';
        }

        $query = $model::query()->where('slug', 'like', $slug.'%');

        if ($currentId !== null) {
            $query->whereKeyNot($currentId);
        }

        // Keyed by the slug itself, so the isset() below is O(1) and a
        // duplicate in the table cannot confuse the counter.
        $taken = array_flip($query->pluck('slug')->all());

        if (! isset($taken[$slug])) {
            return $slug;
        }

        $counter = 1;

        while (isset($taken[$slug.'-'.$counter])) {
            $counter++;
        }

        return $slug.'-'.$counter;
    }
}
