<?php
/**
 * Foredog — Master Scraper Orchestrator
 *
 * Runs all state scrapers in sequence.
 * Usage:
 *   php cron/scrape_all_states.php
 *
 * Cron (daily at 3am):
 *   0 3 * * * /usr/bin/php /path/to/foredog/cron/scrape_all_states.php >> /var/log/foredog_scraper.log 2>&1
 */

$scrapers = [
    'California' => __DIR__ . '/scrape_california.php',
    'Texas'      => __DIR__ . '/scrape_texas.php',
    'New York'   => __DIR__ . '/scrape_newyork.php',
    'Washington' => __DIR__ . '/scrape_washington.php',
    'Florida'    => __DIR__ . '/scrape_florida.php',
    'Illinois'   => __DIR__ . '/scrape_illinois.php',
];

$startTime = microtime(true);
echo "╔══════════════════════════════════════════╗\n";
echo "║     Foredog Master Scraper               ║\n";
echo "║     " . date('Y-m-d H:i:s') . "                    ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

foreach ($scrapers as $state => $file) {
    if (!file_exists($file)) {
        echo "⚠  $state — file not found: $file\n\n";
        continue;
    }
    require $file;
    echo "\n" . str_repeat('─', 50) . "\n\n";
}

$elapsed = round(microtime(true) - $startTime, 1);
echo "✓ All scrapers complete in {$elapsed}s\n";