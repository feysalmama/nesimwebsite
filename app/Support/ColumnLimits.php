<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * How much text a column will actually hold, read from the database instead of
 * assumed.
 *
 * Both admin components used to derive their `max:` rules from a fact about the
 * schema that stopped being true: Prisma mapped a bare `String` to varchar(191),
 * so single-line fields were capped at 191 and textareas were left open. That
 * assumption is what allowed thirty-odd rows to be silently truncated in the
 * first place, and the eighty content columns it described are TEXT now — so a
 * hardcoded 191 would reject input the column accepts, and a hardcoded 65535
 * would accept input a column that is still varchar(191) cannot store.
 *
 * Asking INFORMATION_SCHEMA means neither number is written down anywhere, and
 * widening a column later needs no code change.
 */
final class ColumnLimits
{
    /**
     * Per-table results, so a form validates against one query rather than one
     * per field. Keyed by table name; the value is column => character ceiling.
     *
     * @var array<string, array<string, int>>
     */
    private static array $cache = [];

    /**
     * The character ceiling for one column, or $fallback when the column does
     * not exist or is not a string type — an integer or a DateTime has no
     * character limit to enforce, and the caller's own rule covers it.
     */
    public static function get(string $table, string $column, int $fallback = 191): int
    {
        return self::for($table)[$column] ?? $fallback;
    }

    /**
     * @return array<string, int>
     */
    public static function for(string $table): array
    {
        return self::$cache[$table] ??= self::read($table);
    }

    /**
     * @return array<string, int>
     */
    private static function read(string $table): array
    {
        try {
            $rows = DB::select(
                'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH AS max_length
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$table],
            );
        } catch (\Throwable) {
            /*
             * A database that cannot be introspected must not take the form
             * down with it; the caller's fallback is a sane ceiling and the
             * write itself is still protected by strict mode.
             */
            return [];
        }

        $limits = [];

        foreach ($rows as $row) {
            $length = $row->max_length === null ? null : (int) $row->max_length;

            if ($length === null) {
                continue;
            }

            /*
             * CHARACTER_MAXIMUM_LENGTH means characters for char and varchar,
             * which is what `max:` counts. For the TEXT family it reports the
             * byte size of the type, and a byte is not a character: this
             * database holds Amharic and Afaan Oromoo text, three bytes per
             * character in UTF-8. Dividing by three gives the ceiling that is
             * true for the worst case, so a rule derived from it can never pass
             * a value the column would then have to truncate.
             */
            $isTextFamily = in_array($row->DATA_TYPE, ['tinytext', 'text', 'mediumtext', 'longtext'], true);

            $limits[$row->COLUMN_NAME] = $isTextFamily ? intdiv($length, 3) : $length;
        }

        return $limits;
    }
}
