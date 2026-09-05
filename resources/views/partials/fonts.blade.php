{{--
    next/font self-hosted Fraunces, Inter and JetBrains Mono at build time.
    Blade has no build-time font pipeline, so these come from Google Fonts.
    Noto Sans Ethiopic covers Ge'ez, which none of the three do, and Amiri
    backs the .font-arabic class used by the Islamic message break.

    Shared by the public layout and the admin layout. Both use font-display,
    font-body and font-accent, and keeping the request in one file means the
    family list cannot drift apart between the site and its CMS.
--}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500..700;1,9..144,500..700&family=Inter:wght@400..700&family=JetBrains+Mono:wght@700&family=Noto+Sans+Ethiopic:wght@400..700&family=Amiri:wght@400;700&display=swap">
