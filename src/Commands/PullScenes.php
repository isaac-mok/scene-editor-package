<?php

namespace Bigmom\SceneEditor\Commands;

use Bigmom\SceneEditor\Models\Scene;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class PullScenes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scene:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull scenes from scene editor';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = config('scene-editor.url');
        $apiKey = config('scene-editor.api-key');

        if (! $apiKey) {
            throw new InvalidArgumentException('API Key not found. Please ensure that the environment variable SCENE_EDITOR_API_KEY is set.', 1);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer $apiKey",
            ])->get($url);

            $scenes = collect(json_decode($response->body(), true));

            DB::transaction(function () use ($scenes) {
                foreach ($scenes as $scene) {
                    Scene::updateOrCreate(
                        [
                            'name' => $scene['name'],
                        ],
                        [
                            'type' => $scene['type'],
                            'url' => $scene['url'],
                            'clickable_areas' => $scene['clickableAreas'],
                            'extra_data' => $scene['extraData'],
                        ]
                    );
                }
            });

            $this->info('Success!');
            $this->info('Total number of scenes: '.$scenes->count());
            $this->info('Scenes pulled:');
            $this->info(join(', ', $scenes->pluck('name')->toArray()));
        } catch (Exception $e) {
            throw $e;
        }

        return 0;
    }
}
