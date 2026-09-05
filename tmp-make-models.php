<?php

/**
 * One-off generator: emits an Eloquent model for every remaining table in the
 * existing Prisma schema. Table names are lowercase (MySQL on Windows folds
 * identifiers), keys are cuid strings, timestamp columns are camelCase.
 * Deleted immediately after running.
 */
$dir = __DIR__.'/app/Models';

// class => [table, timestamps, casts, relations]
//   timestamps: both | created | updated | none
//   relation:   [method, kind, RelatedClass, arg3, arg4]
//     BelongsTo / HasMany -> arg3 = foreignKey
//     BelongsToMany       -> arg3 = pivot table, arg4 = [foreignPivotKey, relatedPivotKey]
$models = [
    'ImpactStat' => ['impactstat', 'none', ['value' => 'integer', 'order' => 'integer', 'active' => 'boolean'], []],
    'Program' => ['program', 'both', ['published' => 'boolean', 'order' => 'integer'], []],
    'Service' => ['service', 'both', ['featured' => 'boolean', 'published' => 'boolean', 'order' => 'integer'], []],
    'Project' => ['project', 'both', [
        'published' => 'boolean', 'featured' => 'boolean', 'order' => 'integer',
        'startDate' => 'datetime', 'endDate' => 'datetime',
    ], [
        ['category', 'BelongsTo', 'ProjectCategory', 'categoryId'],
        ['images', 'HasMany', 'ProjectImage', 'projectId'],
    ]],
    'ProjectCategory' => ['projectcategory', 'none', [], [
        ['projects', 'HasMany', 'Project', 'categoryId'],
    ]],
    'ProjectImage' => ['projectimage', 'none', ['order' => 'integer'], [
        ['project', 'BelongsTo', 'Project', 'projectId'],
    ]],
    'Testimonial' => ['testimonial', 'created', ['rating' => 'integer', 'published' => 'boolean', 'order' => 'integer'], []],
    'Partner' => ['partner', 'created', ['order' => 'integer', 'active' => 'boolean'], []],
    'NewsPost' => ['newspost', 'both', ['published' => 'boolean', 'featured' => 'boolean', 'publishedAt' => 'datetime'], [
        ['newsCategory', 'BelongsTo', 'NewsCategory', 'newsCategoryId'],
    ]],
    'NewsCategory' => ['newscategory', 'none', [], [
        ['posts', 'HasMany', 'NewsPost', 'newsCategoryId'],
    ]],
    // Prisma's implicit m2t table _BlogPostToTag has columns A and B, where A
    // points at the alphabetically-first model (BlogPost) and B at Tag.
    'BlogPost' => ['blogpost', 'both', ['published' => 'boolean', 'featured' => 'boolean', 'publishedAt' => 'datetime'], [
        ['author', 'BelongsTo', 'User', 'authorId'],
        ['category', 'BelongsTo', 'BlogCategory', 'categoryId'],
        ['tags', 'BelongsToMany', 'Tag', '_blogposttotag', ['A', 'B']],
    ]],
    'BlogCategory' => ['blogcategory', 'none', [], [
        ['posts', 'HasMany', 'BlogPost', 'categoryId'],
    ]],
    'Tag' => ['tag', 'none', [], [
        ['posts', 'BelongsToMany', 'BlogPost', '_blogposttotag', ['B', 'A']],
    ]],
    'Gallery' => ['gallery', 'created', ['published' => 'boolean', 'eventDate' => 'datetime'], [
        ['images', 'HasMany', 'GalleryImage', 'galleryId'],
    ]],
    'GalleryImage' => ['galleryimage', 'none', ['order' => 'integer'], [
        ['gallery', 'BelongsTo', 'Gallery', 'galleryId'],
    ]],
    'Resource' => ['resource', 'created', ['published' => 'boolean', 'publishedAt' => 'datetime', 'downloadCount' => 'integer'], [
        ['category', 'BelongsTo', 'ResourceCategory', 'categoryId'],
    ]],
    'ResourceCategory' => ['resourcecategory', 'none', [], [
        ['resources', 'HasMany', 'Resource', 'categoryId'],
    ]],
    'FaqItem' => ['faqitem', 'none', ['order' => 'integer', 'published' => 'boolean'], []],
    'TeamMember' => ['teammember', 'created', ['isLeader' => 'boolean', 'order' => 'integer', 'published' => 'boolean'], []],
    'AboutContent' => ['aboutcontent', 'updated', [], []],
    'LandingContent' => ['landingcontent', 'updated', [], []],
    'PresidentMessage' => ['presidentmessage', 'updated', [], []],
    'ActivityLog' => ['activitylog', 'created', [], [
        ['user', 'BelongsTo', 'User', 'userId'],
    ]],
    'MembershipCategory' => ['membershipcategory', 'none', ['published' => 'boolean', 'order' => 'integer'], [
        ['applications', 'HasMany', 'MembershipApplication', 'categoryId'],
    ]],
    'MembershipApplication' => ['membershipapplication', 'both', ['dob' => 'datetime'], [
        ['category', 'BelongsTo', 'MembershipCategory', 'categoryId'],
    ]],
    'VolunteerApplication' => ['volunteerapplication', 'created', [], []],
    'RegistrationSubmission' => ['registrationsubmission', 'created', [], []],
    'ContactMessage' => ['contactmessage', 'created', [], []],
    'DonationIntent' => ['donationintent', 'created', ['amount' => 'integer'], []],
];

// Which query scopes each model gets, mirroring lib/content.ts.
$hasPublished = ['Program', 'Service', 'Project', 'Testimonial', 'NewsPost', 'BlogPost', 'Gallery', 'Resource', 'FaqItem', 'TeamMember', 'MembershipCategory'];
$hasActive = ['ImpactStat', 'Partner'];
$hasOrder = ['ImpactStat', 'Program', 'Service', 'Project', 'Testimonial', 'Partner', 'GalleryImage', 'ProjectImage', 'FaqItem', 'TeamMember', 'MembershipCategory'];

$tsLine = [
    'both' => '',
    'created' => "    const UPDATED_AT = null;\n\n",
    'updated' => "    const CREATED_AT = null;\n\n",
    'none' => "    public \$timestamps = false;\n\n",
];

$made = 0;

foreach ($models as $class => [$table, $ts, $casts, $relations]) {
    $uses = [];
    $kinds = array_unique(array_column($relations, 1));
    foreach ($kinds as $kind) {
        $uses[] = "use Illuminate\\Database\\Eloquent\\Relations\\{$kind};";
    }
    if (in_array($class, $hasPublished, true) || in_array($class, $hasActive, true)) {
        $uses[] = 'use Illuminate\Database\Eloquent\Builder;';
    }
    sort($uses);

    $body = $tsLine[$ts];
    $body .= "    protected \$table = '".$table."';\n";

    if ($casts) {
        $body .= "\n    protected function casts(): array\n    {\n        return [\n";
        foreach ($casts as $col => $type) {
            $body .= "            '".$col."' => '".$type."',\n";
        }
        $body .= "        ];\n    }\n";
    }

    foreach ($relations as $rel) {
        [$method, $kind, $related, $arg3] = $rel;
        $body .= "\n    public function ".$method."(): ".$kind."\n    {\n";
        if ($kind === 'BelongsToMany') {
            [$fk, $rk] = $arg3 === '_blogposttotag' ? $rel[4] : $rel[4];
            $body .= "        return \$this->belongsToMany(".$related."::class, '".$arg3."', '".$fk."', '".$rk."');\n";
        } elseif ($kind === 'BelongsTo') {
            $body .= "        return \$this->belongsTo(".$related."::class, '".$arg3."');\n";
        } else {
            $body .= "        return \$this->hasMany(".$related."::class, '".$arg3."');\n";
        }
        $body .= "    }\n";
    }

    if (in_array($class, $hasPublished, true)) {
        $order = in_array($class, $hasOrder, true) ? "->orderBy('order')" : '';
        $body .= "\n    /** Only published rows reach the public site (lib/content.ts). */\n";
        $body .= "    public function scopePublished(Builder \$query): Builder\n    {\n";
        $body .= "        return \$query->where('published', true)".$order.";\n    }\n";
    }

    if (in_array($class, $hasActive, true)) {
        $order = in_array($class, $hasOrder, true) ? "->orderBy('order')" : '';
        $body .= "\n    public function scopeActive(Builder \$query): Builder\n    {\n";
        $body .= "        return \$query->where('active', true)".$order.";\n    }\n";
    }

    $out = "<?php\n\nnamespace App\\Models;\n\n";
    if ($uses) {
        $out .= implode("\n", $uses)."\n\n";
    }
    $out .= "/**\n";
    $out .= " * Table `".$table."` from prisma/schema.prisma (model ".$class.").\n";
    $out .= " */\n";
    $out .= "class ".$class." extends BaseModel\n{\n";
    $out .= $body;
    $out .= "}\n";

    file_put_contents($dir.'/'.$class.'.php', $out);
    $made++;
}

echo 'generated '.$made." models\n";
