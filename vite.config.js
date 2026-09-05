import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/*
 * The scaffold's bunny('Instrument Sans') font plugin is gone on purpose. The
 * site's faces — Fraunces, Inter, JetBrains Mono, Noto Sans Ethiopic and Amiri —
 * come from partials/fonts.blade.php, which is what the Next.js app loaded, and
 * Amharic in particular needs Noto Sans Ethiopic rather than anything Bunny
 * self-hosts. Keeping it meant fetching a font nobody uses on every build, and
 * failing the build outright when that fetch could not be reached.
 */
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    /*
     * This app sits inside the Next.js project it is replacing, and D:\nesim has
     * its own postcss.config.js wiring up Tailwind v3. Vite searches for a
     * PostCSS config upward from the project root, found that one, and ran v3
     * over resources/css/app.css — which is v4 syntax, so the build died on
     * "`@layer base` is used but no matching `@tailwind base` directive".
     *
     * Declaring an empty plugin list stops the search here. Tailwind v4 needs no
     * PostCSS config at all: @tailwindcss/vite above is the whole integration.
     */
    css: {
        postcss: {
            plugins: [],
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
