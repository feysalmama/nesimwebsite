<?php
// Temporary: proves the Eloquent models read the existing Prisma/MySQL data.
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = App\Models\GlobalSettings::current();
echo 'settings.orgName : '.$s->orgName.PHP_EOL;
echo 'settings.shortName: '.$s->shortName.PHP_EOL;
echo 'settings.email   : '.$s->email.PHP_EOL;
echo 'settings.logo    : '.$s->logoOrDefault().PHP_EOL;

$slides = App\Models\HeroSlide::active()->get();
echo PHP_EOL.'hero slides (active, ordered): '.count($slides).PHP_EOL;
foreach ($slides as $i => $sl) {
    echo '  '.$sl->order.'. en="'.$sl->text('title', 'en').'"'.PHP_EOL;
    echo '     am len='.mb_strlen($sl->text('title', 'am')).'  om len='.mb_strlen($sl->text('title', 'om')).PHP_EOL;
    echo '     img='.$sl->imageUrl.'  active='.var_export($sl->active, true).PHP_EOL;
}

echo PHP_EOL.'programs (published): '.App\Models\Program::published()->count().PHP_EOL;
foreach (App\Models\Program::published()->get() as $p) {
    echo '  - "'.$p->text('title').'" icon='.$p->icon.PHP_EOL;
}

$u = App\Models\User::first();
echo PHP_EOL.'user: '.$u->email.'  role='.$u->role.'  isStaff='.var_export($u->isStaff(), true).PHP_EOL;
echo 'bcrypt $2a$ via User::verifyPassword (correct pw): '
    .var_export($u->verifyPassword('Nesim@2026'), true).PHP_EOL;
echo 'bcrypt $2a$ via User::verifyPassword (wrong pw)  : '
    .var_export($u->verifyPassword('definitely-wrong'), true).PHP_EOL;

echo PHP_EOL.'counts: '
    .'impactstat='.App\Models\ImpactStat::count()
    .' partners='.App\Models\Partner::active()->count()
    .' media='.App\Models\Media::count()
    .' team='.App\Models\TeamMember::published()->count()
    .' services='.App\Models\Service::published()->count()
    .' projects='.App\Models\Project::published()->count()
    .' testimonials='.App\Models\Testimonial::published()->count()
    .' news='.App\Models\NewsPost::published()->count()
    .' faq='.App\Models\FaqItem::published()->count()
    .' islamic='.App\Models\IslamicMessage::active()->count()
    .PHP_EOL;

$bp = App\Models\BlogPost::with(['tags', 'author'])->first();
echo PHP_EOL.'blogpost m2m: '.($bp
    ? '"'.$bp->title.'" tags='.$bp->tags->count().' author='.($bp->author?->name ?? 'null')
    : 'no rows').PHP_EOL;

$lc = App\Models\LandingContent::find('landing-content');
echo 'landingContent reachRegions='.count(App\Support\LocaleText::json($lc?->reachRegions))
    .' factsItems='.count(App\Support\LocaleText::json($lc?->factsItems))
    .' processSteps='.count(App\Support\LocaleText::json($lc?->processSteps))
    .' storiesItems='.count(App\Support\LocaleText::json($lc?->storiesItems)).PHP_EOL;

$ac = App\Models\AboutContent::find('about-content');
echo 'aboutContent: '.($ac ? 'present, storyBody len='.mb_strlen((string) $ac->storyBody) : 'MISSING').PHP_EOL;
echo 'presidentMessage: '.(($p2 = App\Models\PresidentMessage::find('president-message'))
    ? $p2->name.' / '.$p2->position
    : 'MISSING');
echo PHP_EOL;
