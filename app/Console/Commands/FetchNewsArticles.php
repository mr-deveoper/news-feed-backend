<?php

namespace App\Console\Commands;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

/**
 * Fetch News Articles Command
 *
 * Fetches news articles from all configured news API sources
 */
class FetchNewsArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store news articles from all configured news sources';

    /**
     * Execute the console command.
     *
     * @return int Returns Command::SUCCESS on success
     */
    public function handle(NewsAggregatorService $newsAggregator): int
    {
        $this->info('Fetching news articles from all sources...');

        $stats = $newsAggregator->aggregateNews();

        $this->newLine();
        $this->info("Fetched: {$stats['total_fetched']} articles");
        $this->info("Saved: {$stats['total_saved']} new articles");
        $this->info("Skipped: {$stats['total_skipped']} duplicates");

        if ($stats['errors'] > 0) {
            $this->warn("Errors: {$stats['errors']}");
        }

        $this->newLine();
        $this->info('News aggregation completed successfully!');

        return Command::SUCCESS;
    }
}
