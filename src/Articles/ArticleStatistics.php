<?php

declare(strict_types=1);

namespace MyTechIO\Contracts\Articles;

/**
 * Lesender Zugriff auf die BI-Auswertungen (Umsatz, Wareneinsatz, Rohertrag)
 * des Artikel-Bereichs — der Kern implementiert diesen Vertrag über die
 * bestehenden Kern-Tabellen, ein Modul zeigt die Daten nur an.
 */
interface ArticleStatistics
{
    /**
     * Gesamt-Auswertung über alle Artikel im Zeitraum `$from`..`$to`
     * (jeweils `Y-m-d`, inklusive).
     */
    public function overview(string $from, string $to): ArticleOverview;

    /**
     * Detail-Auswertung für einen einzelnen Artikel im Zeitraum, oder
     * `null`, wenn der Artikel nicht existiert.
     */
    public function forArticle(int $articleId, string $from, string $to): ?ArticleDetailStats;
}
