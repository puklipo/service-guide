<?php

namespace App\Jobs;

use App\Models\Area;
use App\Models\Pref;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0))
            ->add(Url::create(route('map')));

        Pref::oldest('id')->lazy()->each(fn (Pref $pref) => $sitemap->add(
            Url::create(url('/?pref='.$pref->id))
        ));

        Area::with('pref')->lazy()->each(function (Area $area) use ($sitemap) {
            $sitemap->add(
                Url::create(url('/?pref='.$area->pref->id.'&area='.$area->id))
            );

            foreach (config('service') as $service_id => $service) {
                $sitemap->add(
                    Url::create(url('/?pref='.$area->pref->id.'&area='.$area->id.'&service='.$service_id))
                );
            }

            return $sitemap;
        });

        $sitemap->writeToDisk(config('filesystems.default'), 'sitemap.xml');
    }
}
