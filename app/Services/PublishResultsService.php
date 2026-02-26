<?php

namespace App\Services;

use App\Models\Term;

/**
 * Publishes term results so parents can view report cards.
 * Sets terms.results_published_at = now().
 */
class PublishResultsService
{
    /**
     * Publish results for a term.
     */
    public function publishTermResults(int $termId): Term
    {
        $term = Term::findOrFail($termId);
        $term->update(['results_published_at' => now()]);

        return $term->fresh();
    }

    /**
     * Check if term results are published.
     */
    public function isPublished(int $termId): bool
    {
        $term = Term::find($termId);

        return $term && $term->results_published_at !== null;
    }
}
