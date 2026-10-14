<?php
$projects = require __DIR__ . '/data/projects.php';
foreach ($projects as $projectIndex => &$project) {
    $project['_original_order'] = $projectIndex;
}
unset($project);

usort($projects, static function (array $a, array $b): int {
    $dateComparison = projectDateTimestamp($b) <=> projectDateTimestamp($a);

    return $dateComparison !== 0
        ? $dateComparison
        : ($a['_original_order'] ?? 0) <=> ($b['_original_order'] ?? 0);
});

$drawings = getDrawingImages(__DIR__ . '/media/img/dessins', './media/img/dessins');
$previewDrawings = array_slice($drawings, 0, 4);
$momentPicks = [
    'music' => [
        'label' => 'son du moment',
        'title' => 'Heroes',
        'meta' => 'David Bowie',
        'note' => 'Le morceau que j\'écoute en boucle en ce moment.',
        'link' => 'https://open.spotify.com/playlist/3nHgohzJZ09ujXbpkJFhDS?si=W7VxWtQVQ_2_Q82PGU1baQ',
        'action' => 'Ouvrir Spotify',
        'icon' => 'fa-brands fa-spotify',
        'image' => './media/moment/music.jpg',
        'image_alt' => 'Pochette stylisée du morceau Under Pressure',
    ],
    'film' => [
        'label' => 'film du moment',
        'title' => 'Jojo Rabbit',
        'meta' => 'Taika Waititi / 2019',
        'note' => 'Le film qui me reste en tête ces derniers temps.',
        'link' => 'https://boxd.it/eNUj9',
        'action' => 'Ouvrir Letterboxd',
        'icon' => 'fa-brands fa-letterboxd',
        'image' => './media/moment/film.jpg',
        'image_alt' => 'Affiche stylisée du film Aftersun',
    ],
];

function getDrawingImages(string $directory, string $publicPath): array
{
    $files = glob($directory . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];

    if (!$files) {
        $files = glob($directory . '/full-webp/*.{webp,jpg,jpeg,png,gif}', GLOB_BRACE) ?: [];
    }

    natsort($files);

    return array_map(
        static function (string $file) use ($directory, $publicPath): string {
            $basePublicPath = rtrim($publicPath, '/');
            $relativePath = str_starts_with($file, rtrim($directory, '/') . '/')
                ? ltrim(substr($file, strlen(rtrim($directory, '/'))), '/')
                : basename($file);

            return $basePublicPath . '/' . $relativePath;
        },
        array_values($files)
    );
}

function drawingThumbPath(string $drawing): string
{
    $directory = rtrim(dirname($drawing), '/');
    $assetsDirectory = basename($directory) === 'full-webp'
        ? rtrim(dirname($directory), '/')
        : $directory;
    $filename = pathinfo($drawing, PATHINFO_FILENAME);
    $candidates = [
        $assetsDirectory . '/thumbs-fast/' . $filename . '.webp',
        $drawing,
    ];

    foreach ($candidates as $candidate) {
        $localThumb = __DIR__ . '/' . ltrim($candidate, './');

        if (is_file($localThumb)) {
            return $candidate;
        }
    }

    return $drawing;
}

function drawingFullPath(string $drawing): string
{
    $directory = rtrim(dirname($drawing), '/');
    $assetsDirectory = basename($directory) === 'full-webp'
        ? rtrim(dirname($directory), '/')
        : $directory;
    $filename = pathinfo($drawing, PATHINFO_FILENAME);
    $candidates = [
        $assetsDirectory . '/full-webp/' . $filename . '.webp',
        $drawing,
    ];

    foreach ($candidates as $candidate) {
        $localImage = __DIR__ . '/' . ltrim($candidate, './');

        if (is_file($localImage)) {
            return $candidate;
        }
    }

    return $drawing;
}

function imageDimensionAttributes(string $publicPath): string
{
    static $cache = [];

    if (!isset($cache[$publicPath])) {
        $localPath = __DIR__ . '/' . ltrim($publicPath, './');
        $size = is_file($localPath) ? @getimagesize($localPath) : false;
        $cache[$publicPath] = $size ? [$size[0], $size[1]] : null;
    }

    if (!$cache[$publicPath]) {
        return '';
    }

    [$width, $height] = $cache[$publicPath];

    return ' width="' . (int) $width . '" height="' . (int) $height . '"';
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function projectDateTimestamp(array $project): int
{
    if (empty($project['date'])) {
        return PHP_INT_MIN;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $project['date'])
        ?: DateTimeImmutable::createFromFormat('!Y-m', $project['date'])
        ?: date_create_immutable($project['date']);

    return $date ? $date->getTimestamp() : PHP_INT_MIN;
}

function projectDateLabel(array $project): string
{
    return $project['date_label'] ?? 'date à compléter';
}

function projectDisplayNumber(int $index): string
{
    return 'planche ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
}

function renderMedia(array $media, bool $forCard = false): void
{
    $src = e($media['src']);
    $alt = e($media['alt']);

    if ($media['type'] === 'video') {
        $controlsList = isset($media['controlslist']) ? ' controlslist="' . e($media['controlslist']) . '"' : '';
        $wrapperClass = $forCard ? 'video' : 'drawer-video';
        echo '<div class="' . $wrapperClass . '"><video src="' . $src . '" controls' . $controlsList . ' aria-label="' . $alt . '"></video></div>';
        return;
    }

    if ($forCard) {
        echo '<figure class="image"><img src="' . $src . '" alt="' . $alt . '" loading="lazy" decoding="async"></figure>';
        return;
    }

    echo '<figure class="drawer-media"><button class="drawer-media-button" type="button" aria-label="Afficher ' . $alt . ' en grand"><img src="' . $src . '" alt="' . $alt . '" loading="lazy" decoding="async"></button></figure>';
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Portfolio de Luka Dombre, étudiant en BUT MMI à Lannion : projets de graphisme, dessin, audiovisuel, web et culture visuelle.">
    <meta name="author" content="Luka Dombre">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="Luka Dombre — Portfolio graphisme, dessin et web">
    <meta property="og:description"
        content="Découvre les projets créatifs de Luka Dombre : dessin, graphisme, édition, audiovisuel et développement web.">
    <meta property="og:image" content="https://lukaillou.github.io/portfolio/media/img/portfolio.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Luka Dombre — Portfolio graphisme, dessin et web">
    <meta name="twitter:description"
        content="Portfolio créatif entre dessin, graphisme, audiovisuel et intégration web.">
    <meta name="twitter:image" content="https://lukaillou.github.io/portfolio/media/img/portfolio.png">
    <link rel="stylesheet" href="./style/css/bulma.min.css">
    <link rel="stylesheet" href="./style/css/css/all.css">
    <link rel="stylesheet" href="./style/style.css">
    <title>Luka Dombre — Portfolio graphisme, dessin et web</title>
    <link rel="icon" href="./media/img/logo.svg">
</head>

<body id="top" class="has-navbar-fixed-top">
    <header>
        <nav class="navbar is-fixed-top" aria-label="Navigation principale">
            <div class="navbar-brand">
                <a class="navbar-item bouton" href="#top" title="Retour à l'accueil">
                    <img src="./media/img/logo.svg" alt="Logo de mon portfolio" style="max-height: 2.5rem;">
                </a>
                <button class="navbar-burger portfolio-burger" type="button" aria-label="Ouvrir le menu"
                    aria-expanded="false" aria-controls="mainNavbar" data-target="mainNavbar">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="navbar-menu" id="mainNavbar">
                <div class="navbar-start">
                    <a class="navbar-item bouton scribble-underline" href="#projets" title="Voir mes projets">Mes
                        projets</a>
                    <a class="navbar-item bouton scribble-underline" href="#recherche"
                        title="Voir ce que je recherche">Alternance</a>
                    <a class="navbar-item bouton scribble-underline" href="#competences"
                        title="Voir mes compétences">Mes
                        compétences</a>
                    <a class="navbar-item bouton scribble-underline" href="#contact"
                        title="Voir les moyens de me contacter">Me contacter</a>
                </div>
                <div class="navbar-end">
                    <div class="navbar-item">
                        <a class="button" href="./media/CV_Luka_Dombre.pdf" target="_blank" title="Télécharger mon CV en PDF">
                            <i class="fa-solid fa-download mr-2" aria-hidden="true"></i>
                            <strong>Mon CV</strong>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="pt-6">
        <section class="column is-five-sixths mx-auto hero-section">
            <div class="hero-grid">
                <div>
                    <p class="hero-role">Étudiant BUT MMI — Lannion</p>
                    <h1 class="hero-name">Luka<br>Dombre</h1>
                    <p class="hero-lede">Je dessine au crayon depuis toujours. Aujourd'hui
                        j'explore le graphisme, l'illustration, l'anatomie — toujours avec le même réflexe :
                        d'abord un trait, puis une idée.</p>
                    <div class="hero-actions">
                        <a class="hero-cta" href="#projets" title="Voir mes projets">
                            <span>Voir mes projets</span>
                            <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="tags are-medium mt-5">
                        <span class="tag">Graphisme</span>
                        <span class="tag">Créativité</span>
                        <span class="tag">Dessin</span>
                    </div>
                </div>
                <figure class="polaroid">
                    <div class="tape"></div>
                    <img src="./media/img/portfolio.png" alt="Photographie et assemblage d'élément de dessins Portfolio">
                    <figcaption>carnet, page 1</figcaption>
                </figure>
            </div>
        </section>

        <section id="recherche" class="search-section" aria-labelledby="search-title">
            <div class="search-shell scroll-reveal">
                <div class="search-copy">
                    <p class="eyebrow">Ce que je recherche</p>
                    <h2 id="search-title" class="title">Un stage entre avril 2027 et juin 2027</h2>
                    <div class="search-notes" aria-label="Informations de recherche">
                        <span><strong>Type</strong> Stage</span>
                        <span><strong>Lieu</strong> Autour de Quimper ou Lannion</span>
                        <span><strong>Disponibilité</strong> Entre le 5 avril et le 18 juin</span>
                    </div>
                </div>
                <div class="search-details" aria-label="Domaines visés">
                    <span><i class="fa-solid fa-pen-nib" aria-hidden="true"></i> Graphisme</span>
                    <span><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Communication</span>
                    <span><i class="fa-solid fa-palette" aria-hidden="true"></i> Illustration</span>
                    <span><i class="fa-solid fa-desktop" aria-hidden="true"></i> Web créatif</span>
                    <span><i class="fa-solid fa-film" aria-hidden="true"></i> Audiovisuel</span>
                </div>
                <div class="search-actions">
                    <a class="search-link search-link--primary" href="#contact">
                        <span>Me contacter</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a class="search-link" href="./media/CV_Luka_Dombre.pdf" target="_blank" rel="noopener">
                        <span>Voir mon CV</span>
                        <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </section>

        <div class="chapter-divider">
            <span class="chapter-tab">Chapitre 01 — Pour mieux me connaître</span>
        </div>

        <section id="about-section" aria-labelledby="about-title" class="about-layout">
            <div class="about-shell">
                <div class="about-heading scroll-reveal">
                    <p class="eyebrow">Carnet personnel</p>
                    <h2 id="about-title" class="title">Pour mieux me connaître</h2>
                    <p>Quelques repères sur ce qui nourrit mon univers : le dessin, les images en mouvement,
                        les outils numériques, et l'envie de raconter quelque chose visuellement.</p>
                </div>

                <div class="about-grid">
                    <article class="about-story scroll-reveal reveal-left">
                        <p class="story-label">portrait rapide</p>
                        <p>J'aime énormément dessiner ce qui m'entoure. J'ai commencé par de simples dessins
                            d'enfants comme tout le monde. Depuis le collège, j'explore de nombreux médias
                            artistiques.</p>
                        <p>Aujourd’hui, je me concentre surtout sur le dessin traditionnel au crayon. J’apprends
                            l’anatomie pour mieux comprendre les proportions, les formes et le mouvement du corps, tout
                            en développant mon sens de l’observation et ma précision.
                        </p>
                        <p>Mon rêve : mêler mes talents en dessin avec des domaines créatifs et visuels. Grâce aux
                            outils numériques et à mes connaissances en développement, c'est possible !</p>
                        <p>Moi, Luka Dombre, je suis étudiant en première année de BUT Métiers du Multimédia et de
                            l'Internet à Lannion. Retenez bien ce nom !</p>
                        <div class="about-stamp">
                            <img src="./media/img/continued.png" alt="Flèche To Be Continued qui pointe vers la gauche">
                            <span class="note">toujours une idée en cours</span>
                        </div>
                    </article>

                    <aside class="about-side" aria-label="Passions et formation">
                        <div class="passion-card scroll-reveal reveal-right reveal-delay-1">
                            <div class="passion-topline">
                                <p class="story-label">inspirations</p>
                                <div class="passion-controls">
                                    <button class="button is-ghost px-2" id="prevBtn" title="Passion précédente"
                                        aria-label="Passion précédente">
                                        <span class="icon is-medium"><i class="fas fa-chevron-left"
                                                aria-hidden="true"></i></span>
                                    </button>
                                    <button class="button is-ghost px-2" id="nextBtn" title="Passion suivante"
                                        aria-label="Passion suivante">
                                        <span class="icon is-medium"><i class="fas fa-chevron-right"
                                                aria-hidden="true"></i></span>
                                    </button>
                                </div>
                            </div>
                            <div id="slider-content" class="has-text-centered">

                                <article class="slide is-active" aria-hidden="false">
                                    <h3 class="title is-size-4">Art</h3>
                                    <a class="passion-link" href="#" id="open-dessins" title="Voir mes dessins">
                                        <div class="sketch-preview" aria-hidden="true">
                                            <?php foreach ($previewDrawings as $drawing): ?>
                                                <?php $previewThumb = drawingThumbPath($drawing); ?>
                                                <figure>
                                                    <img src="<?= e($previewThumb) ?>"
                                                        <?= imageDimensionAttributes($previewThumb) ?> alt=""
                                                        loading="eager" decoding="async" fetchpriority="low">
                                                </figure>
                                            <?php endforeach; ?>
                                            <span class="sketch-count"><?= count($drawings) ?> dessins</span>
                                        </div>
                                        <span class="passion-action">
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                            Voir mes dessins
                                        </span>
                                    </a>
                                </article>

                                <article class="slide" aria-hidden="true">
                                    <?php $musicPick = $momentPicks['music']; ?>
                                    <h3 class="title is-size-4">Musique</h3>
                                    <a class="moment-pick moment-pick--music" href="<?= e($musicPick['link']) ?>"
                                        title="<?= e($musicPick['action']) ?>" target="_blank" rel="noopener">
                                        <span class="moment-art">
                                            <img src="<?= e($musicPick['image']) ?>"
                                                alt="<?= e($musicPick['image_alt']) ?>" loading="lazy" decoding="async">
                                        </span>
                                        <span class="moment-copy">
                                            <span class="moment-label"><?= e($musicPick['label']) ?></span>
                                            <strong><?= e($musicPick['title']) ?></strong>
                                            <span class="moment-meta"><?= e($musicPick['meta']) ?></span>
                                            <span class="moment-note"><?= e($musicPick['note']) ?></span>
                                            <span class="passion-action">
                                                <i class="fa-solid fa-play" aria-hidden="true"></i>
                                                <?= e($musicPick['action']) ?>
                                            </span>
                                        </span>
                                    </a>
                                </article>

                                <article class="slide" aria-hidden="true">
                                    <?php $filmPick = $momentPicks['film']; ?>
                                    <h3 class="title is-size-4">Cinéma</h3>
                                    <a class="moment-pick moment-pick--film" href="<?= e($filmPick['link']) ?>"
                                        title="<?= e($filmPick['action']) ?>" target="_blank" rel="noopener">
                                        <span class="moment-art">
                                            <img src="<?= e($filmPick['image']) ?>"
                                                alt="<?= e($filmPick['image_alt']) ?>" loading="lazy" decoding="async">
                                        </span>
                                        <span class="moment-copy">
                                            <span class="moment-label"><?= e($filmPick['label']) ?></span>
                                            <strong><?= e($filmPick['title']) ?></strong>
                                            <span class="moment-meta"><?= e($filmPick['meta']) ?></span>
                                            <span class="moment-note"><?= e($filmPick['note']) ?></span>
                                            <span class="passion-action">
                                                <i class="fa-solid fa-clapperboard" aria-hidden="true"></i>
                                                <?= e($filmPick['action']) ?>
                                            </span>
                                        </span>
                                    </a>
                                </article>

                            </div>
                            <div class="is-flex is-justify-content-center mt-5" id="dots"></div>
                        </div>

                        <div id="modal-dessins" class="modal drawings-modal" aria-hidden="true">
                            <div class="modal-background"></div>
                            <div class="modal-card drawings-modal-card">
                                <header class="drawings-modal-head">
                                    <div>
                                        <p class="story-label">carnet ouvert</p>
                                        <h2 class="modal-card-title">Mes dessins</h2>
                                        <p><?= count($drawings) ?> dessins et croquis.</p>
                                    </div>
                                    <button class="drawer-close drawings-close" id="close-modal-dessins"
                                        aria-label="Fermer la galerie de dessins">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </header>
                                <section class="modal-card-body drawings-modal-body">
                                    <?php if ($drawings): ?>
                                        <div class="sketch-gallery">
                                            <?php foreach ($drawings as $index => $drawing): ?>
                                                <?php
                                                $drawingName = pathinfo($drawing, PATHINFO_FILENAME);
                                                $drawingThumb = drawingThumbPath($drawing);
                                                ?>
                                                <figure class="sketch-card">
                                                    <button class="sketch-card-button" type="button"
                                                        data-full="<?= e(drawingFullPath($drawing)) ?>"
                                                        aria-label="Afficher le dessin <?= $index + 1 ?> en grand">
                                                        <img src="<?= e($drawingThumb) ?>"
                                                            <?= imageDimensionAttributes($drawingThumb) ?>
                                                            loading="<?= $index < 24 ? 'eager' : 'lazy' ?>"
                                                            decoding="async"<?= $index < 24 ? ' fetchpriority="low"' : '' ?>
                                                            alt="Dessin <?= e($drawingName) ?>">
                                                    </button>
                                                    <figcaption>Dessin <?= $index + 1 ?></figcaption>
                                                </figure>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="drawer-intro">Aucun dessin n'a encore été ajouté dans le dossier.</p>
                                    <?php endif; ?>
                                </section>
                            </div>
                        </div>
                        <div id="drawings-lightbox" class="project-lightbox drawings-lightbox" role="dialog"
                            aria-modal="true" aria-hidden="true" aria-label="Dessin agrandi">
                            <button class="lightbox-close" id="closeDrawingLightbox" title="Fermer"
                                aria-label="Fermer le dessin agrandi">
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                            <button class="lightbox-nav lightbox-prev" id="prevDrawingLightbox" title="Dessin précédent"
                                aria-label="Dessin précédent">
                                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                            </button>
                            <figure class="lightbox-figure">
                                <p class="lightbox-counter" id="drawingLightboxCounter"></p>
                                <img id="drawingLightboxImage" alt="">
                                <figcaption id="drawingLightboxCaption"></figcaption>
                            </figure>
                            <button class="lightbox-nav lightbox-next" id="nextDrawingLightbox" title="Dessin suivant"
                                aria-label="Dessin suivant">
                                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="formation-card scroll-reveal reveal-right reveal-delay-2">
                            <p class="story-label">formation</p>
                            <div class="timeline">
                                <article>
                                    <span>2025 - aujourd'hui</span>
                                    <h3>BUT Métiers du Multimédia et de l'Internet</h3>
                                    <p>IUT de Lannion, Lannion</p>
                                </article>
                                <article>
                                    <span>2022 - 2025</span>
                                    <h3>Baccalauréat Général | Mention Bien</h3>
                                    <p>Spécialités Mathématiques, Numérique et Sciences Informatiques</p>
                                    <p>Lycée Cornouaille, Quimper</p>
                                </article>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <section id="projets" aria-label="Mes projets" class="py-5">
            <div class="chapter-divider">
                <span class="chapter-tab">Chapitre 02 — Projets</span>
            </div>
            <h2 class="title has-text-centered">Mes projets</h2>
            <p class="section-lede">Quelques planches de mon book.</p>

            <div class="projects-grid">
                <?php foreach ($projects as $projectIndex => $project): ?>
                    <?php $displayNumber = projectDisplayNumber($projectIndex); ?>
                    <article class="planche project-card reveal">
                        <div class="card-image">
                            <p class="project-number"><?= e($displayNumber) ?></p>
                            <?php renderMedia($project['card_media'], true); ?>
                        </div>
                        <div class="card-content">
                            <p class="project-category"><?= e($project['category']) ?></p>
                            <?php if (!empty($project['date'])): ?>
                                <time class="project-date" datetime="<?= e($project['date']) ?>">
                                    <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                                    <?= e(projectDateLabel($project)) ?>
                                </time>
                            <?php else: ?>
                                <p class="project-date">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    <?= e(projectDateLabel($project)) ?>
                                </p>
                            <?php endif; ?>
                            <h2><?= e($project['title']) ?></h2>
                            <p class="planche-teaser"><?= e($project['teaser']) ?></p>
                            <div class="tags are-small mb-3">
                                <?php foreach ($project['tags'] as $tag): ?>
                                    <span class="tag"><?= e($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="#" class="button voir-projet" data-projet="<?= e($project['id']) ?>"
                                title="Voir le détail du projet <?= e($project['title']) ?>">
                                <span>Ouvrir la planche</span>
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php foreach ($projects as $projectIndex => $project): ?>
                <?php $displayNumber = projectDisplayNumber($projectIndex); ?>
                <div id="projet-<?= e($project['id']) ?>-content" hidden data-title="<?= e($project['detail']['title']) ?>"
                    data-number="<?= e($displayNumber) ?>" data-date="<?= e(projectDateLabel($project)) ?>"
                    data-category="<?= e($project['category']) ?>">
                    <p class="drawer-intro"><?= e($project['detail']['intro']) ?></p>

                    <?php if (!empty($project['info'])): ?>
                        <?php
                        $projectInfo = $project['info'];
                        $projectTools = $projectInfo['tools'] ?? [];
                        $projectFinalLink = $projectInfo['final_link'] ?? null;
                        ?>
                        <div class="project-facts" aria-label="Informations du projet">
                            <article>
                                <span>Rôle personnel</span>
                                <p><?= e($projectInfo['role'] ?? 'À compléter') ?></p>
                            </article>
                            <article>
                                <span>Logiciels / outils</span>
                                <p><?= e($projectTools ? implode(', ', $projectTools) : 'À compléter') ?></p>
                            </article>
                            <article>
                                <span>Durée</span>
                                <p><?= e($projectInfo['duration'] ?? 'À compléter') ?></p>
                            </article>
                            <article>
                                <span>Contexte</span>
                                <p><?= e($projectInfo['context'] ?? 'À compléter') ?></p>
                            </article>
                            <?php if (!empty($projectFinalLink['url'])): ?>
                                <a class="project-final-link" href="<?= e($projectFinalLink['url']) ?>" target="_blank"
                                    rel="noopener">
                                    <i class="fa-solid fa-up-right-from-square" aria-hidden="true"></i>
                                    <?= e($projectFinalLink['label'] ?? 'Voir le rendu final') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="drawer-sections">
                        <?php foreach ($project['detail']['sections'] as $section): ?>
                            <section class="drawer-section">
                                <h4 class="title is-5"><?= e($section['title']) ?></h4>
                                <ul>
                                    <?php foreach ($section['items'] as $item): ?>
                                        <li><?= e($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($project['detail']['media'])): ?>
                        <div class="drawer-gallery">
                            <?php foreach ($project['detail']['media'] as $media): ?>
                                <?php renderMedia($media); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <figure class="closing-doodle">
                <img src="./media/img/icarus.png" alt="Illustration d'Icare qui tombe">
                <figcaption class="note">fin de planches.</figcaption>
            </figure>

            <aside id="drawer" class="project-drawer" role="dialog" aria-modal="true" aria-hidden="true"
                aria-labelledby="drawer-titre">
                <div class="drawer-tape" aria-hidden="true"></div>
                <button class="drawer-close" id="closeDrawer" title="Fermer" aria-label="Fermer le panneau">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
                <header class="drawer-header">
                    <p class="drawer-meta" id="drawer-meta">planche</p>
                    <h2 class="title" id="drawer-titre">Détail du projet</h2>
                </header>
                <div id="drawer-contenu" class="drawer-content"></div>
            </aside>
            <div id="overlay"></div>
            <div id="project-lightbox" class="project-lightbox" role="dialog" aria-modal="true" aria-hidden="true"
                aria-label="Image du projet agrandie">
                <button class="lightbox-close" id="closeLightbox" title="Fermer" aria-label="Fermer l'image agrandie">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
                <button class="lightbox-nav lightbox-prev" id="prevLightbox" title="Image précédente"
                    aria-label="Image précédente">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <figure class="lightbox-figure">
                    <p class="lightbox-counter" id="lightboxCounter"></p>
                    <img id="lightboxImage" alt="">
                    <figcaption id="lightboxCaption"></figcaption>
                </figure>
                <button class="lightbox-nav lightbox-next" id="nextLightbox" title="Image suivante"
                    aria-label="Image suivante">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>

        </section>

        <section id="competences" aria-labelledby="skills-title" class="skills-section torn-top">
            <div class="skills-shell">
                <div class="skills-heading scroll-reveal">
                    <p class="eyebrow">Boîte à outils</p>
                    <h2 id="skills-title" class="title">Mes compétences</h2>
                    <p>Une sélection d'outils et de réflexes que j'utilise pour passer d'une idée griffonnée à une
                        production visuelle, web ou audiovisuelle.</p>
                </div>

                <div class="skills-board">
                    <article class="tool-panel scroll-reveal reveal-left">
                        <div class="panel-heading">
                            <span class="panel-number">01</span>
                            <div>
                                <p class="story-label">création visuelle</p>
                                <h3>Images, maquettes et pixel art</h3>
                            </div>
                        </div>
                        <div class="tool-grid">
                            <div class="tool-card">
                                <img src="./media/icons/devicon-illustrator.svg" alt="Adobe Illustrator">
                                <span>Illustrator</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/devicon-photoshop.svg" alt="Adobe Photoshop">
                                <span>Photoshop</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logo-ibispaint.jpg" alt="Ibis Paint X">
                                <span>Ibis Paint X</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/devicon-figma.svg" alt="Figma">
                                <span>Figma</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logo-canva.svg" alt="Canva">
                                <span>Canva</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logo-aseprite.svg" alt="Aseprite">
                                <span>Aseprite</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logos-procreate.png" alt="Procreate">
                                <span>Procreate</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logo-dreams.svg" alt="Procreate Dreams">
                                <span>Dreams</span>
                            </div>
                        </div>
                    </article>

                    <article class="tool-panel tool-panel--dark scroll-reveal reveal-right reveal-delay-1">
                        <div class="panel-heading">
                            <span class="panel-number">02</span>
                            <div>
                                <p class="story-label">développement</p>
                                <h3>Intégration et logique web</h3>
                            </div>
                        </div>
                        <div class="tool-grid">
                            <div class="tool-card">
                                <img src="./media/icons/logos-html-5.png" alt="HTML5">
                                <span>HTML5</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logos-css-3.png" alt="CSS3">
                                <span>CSS3</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/javascript.png" alt="JavaScript">
                                <span>JavaScript</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logos-bulma.svg" alt="Bulma">
                                <span>Bulma</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logo-python.png" alt="Python">
                                <span>Python</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logo-php.png" alt="PHP">
                                <span>PHP</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/logo-sql.png" alt="SQL">
                                <span>SQL</span>
                            </div>
                            <div class="tool-card">
                                <img src="./media/icons/devicon-vscode.svg" alt="VS Code">
                                <span>VS Code</span>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="skills-method scroll-reveal reveal-delay-2">
                    <span class="note">ma façon de travailler</span>
                    <p>Observer, esquisser, tester, corriger. J'aime garder une trace de chaque étape pour comprendre
                        ce qui fonctionne et rendre le résultat plus clair.</p>
                </div>

                <div class="soft-skills-grid">
                    <article class="soft-skill-card scroll-reveal">
                        <i class="fas fa-lightbulb" aria-hidden="true"></i>
                        <h3>Créativité</h3>
                        <p>Imaginer des solutions originales et explorer plusieurs pistes visuelles.</p>
                    </article>
                    <article class="soft-skill-card scroll-reveal reveal-delay-1">
                        <i class="fas fa-fire" aria-hidden="true"></i>
                        <h3>Persévérance</h3>
                        <p>Reprendre, ajuster et chercher jusqu'à trouver une direction qui tient.</p>
                    </article>
                    <article class="soft-skill-card scroll-reveal reveal-delay-2">
                        <i class="fas fa-user-check" aria-hidden="true"></i>
                        <h3>Autonomie</h3>
                        <p>M'organiser, avancer seul et garder un projet lisible du début à la fin.</p>
                    </article>
                    <article class="soft-skill-card scroll-reveal reveal-delay-3">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <h3>Curiosité</h3>
                        <p>Tester de nouveaux outils, médias et domaines créatifs pour progresser.</p>
                    </article>
                </div>
            </div>
        </section>

    </main>

    <footer id="contact" class="footer contact-section torn-top">
        <div class="contact-shell">
            <div class="contact-heading scroll-reveal">
                <p class="eyebrow">Page suivante</p>
                <h2 class="title">Me contacter</h2>
                <p>Un projet, une question ou une envie de discuter création : mes portes sont ouvertes.</p>
            </div>
            <div class="contact-grid">
                <a class="contact-link scroll-reveal" href="tel:+33769350435">
                    <span class="contact-icon"><i class="fa-solid fa-phone" aria-hidden="true"></i></span>
                    <span>
                        <strong>Téléphone</strong>
                        <small>+33 7 69 35 04 35</small>
                    </span>
                </a>
                <a class="contact-link scroll-reveal reveal-delay-1" href="mailto:luka.dombre@gmail.com"
                    target="_blank">
                    <span class="contact-icon"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                    <span>
                        <strong>Email</strong>
                        <small>luka.dombre@gmail.com</small>
                    </span>
                </a>
                <a class="contact-link scroll-reveal reveal-delay-2" href="https://www.linkedin.com/in/luka-dombre"
                    target="_blank" rel="noopener" title="Mon profil LinkedIn">
                    <span class="contact-icon"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></span>
                    <span>
                        <strong>LinkedIn</strong>
                        <small>luka-dombre</small>
                    </span>
                </a>
            </div>
            <p class="note">à bientôt, sur une page blanche !</p>
        </div>
    </footer>

    <a class="back-to-top" id="backToTop" href="#top" title="Retour en haut" aria-label="Retour en haut">
        <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
    </a>

    <script src="./script.js" defer></script>

</body>

</html>
