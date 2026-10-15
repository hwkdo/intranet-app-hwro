<?php

declare(strict_types=1);

use Hwkdo\IntranetAppBase\Enums\BackgroundActivityCadence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('intranet_background_activity_configs') || ! Schema::hasTable('intranet_app_hwro_settings')) {
            return;
        }

        $row = DB::table('intranet_app_hwro_settings')->orderByDesc('version')->first();

        if ($row === null) {
            return;
        }

        $settings = json_decode((string) $row->settings, true);

        if (! is_array($settings)) {
            return;
        }

        $this->copy(
            'hwro.search-betriebsnr',
            (bool) ($settings['scheduleSearchBetriebsnr'] ?? true),
            BackgroundActivityCadence::EveryNMinutes,
            (int) ($settings['scheduleSearchBetriebsnrIntervalMinutes'] ?? 15),
        );

        $this->copy(
            'hwro.make-betriebsakte',
            (bool) ($settings['scheduleMakeBetriebsakte'] ?? true),
            BackgroundActivityCadence::EveryNMinutes,
            (int) ($settings['scheduleMakeBetriebsakteIntervalMinutes'] ?? 15),
        );

        if (array_key_exists('scheduleDeleteVorgaenge', $settings) || array_key_exists('scheduleDeleteVorgaengeAfterWeeks', $settings)) {
            $this->copy(
                'hwro.clean-vorgaenge',
                (bool) ($settings['scheduleDeleteVorgaenge'] ?? true),
                BackgroundActivityCadence::DailyAt,
                1,
                '00:01',
                ['after_weeks' => (int) ($settings['scheduleDeleteVorgaengeAfterWeeks'] ?? 12)],
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('intranet_background_activity_configs')) {
            return;
        }

        DB::table('intranet_background_activity_configs')->whereIn('activity_key', [
            'hwro.search-betriebsnr',
            'hwro.make-betriebsakte',
            'hwro.clean-vorgaenge',
        ])->delete();
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function copy(string $key, bool $enabled, BackgroundActivityCadence $cadence, int $every, ?string $at = null, array $parameters = []): void
    {
        DB::table('intranet_background_activity_configs')->updateOrInsert(
            ['activity_key' => $key],
            [
                'enabled' => $enabled,
                'cadence' => $cadence->value,
                'every' => $every,
                'offset' => 0,
                'at' => $at,
                'parameters' => json_encode($parameters),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
};
