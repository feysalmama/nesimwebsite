<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PresidentMessage;
use App\Models\TeamMember;
use Illuminate\View\View;

/**
 * Port of app/[locale]/leadership/page.tsx.
 *
 * getTeam() returned one ordered list and the page split it in the browser with
 * two filter() calls. Split here instead so the view has two collections to loop
 * and the order within each is the `order` column the CMS sorts by.
 */
class LeadershipController extends Controller
{
    public function __invoke(): View
    {
        $team = TeamMember::published()->get();

        return view('site.leadership', [
            'president' => PresidentMessage::find(PresidentMessage::SINGLETON_ID),
            'leaders' => $team->filter(static fn (TeamMember $member) => $member->isLeader)->values(),
            'members' => $team->reject(static fn (TeamMember $member) => $member->isLeader)->values(),
        ]);
    }
}
