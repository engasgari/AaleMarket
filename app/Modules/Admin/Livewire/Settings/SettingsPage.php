<?php

namespace App\Modules\Admin\Livewire\Settings;

use App\Modules\Admin\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('تنظیمات')]
class SettingsPage extends Component
{
    /** @var array<string, array<string, string>> */
    public array $values = [];

    public function mount(): void
    {
        $this->authorize('settings.update');

        $settings = Setting::query()->orderBy('group')->orderBy('key')->get();

        foreach ($settings as $setting) {
            $this->values[$setting->group][$setting->key] = $this->formatForInput($setting);
        }
    }

    public function save(): void
    {
        $this->authorize('settings.update');

        $settings = Setting::query()->get();

        foreach ($settings as $setting) {
            $group = $setting->group;
            $key = $setting->key;

            if (! isset($this->values[$group][$key])) {
                continue;
            }

            $setting->value = $this->normalizeForStorage($setting, $this->values[$group][$key]);
            $setting->save();
        }

        session()->flash('status', 'تنظیمات ذخیره شد.');
    }

    public function render(): View
    {
        /** @var Collection<string, Collection<int, Setting>> $grouped */
        $grouped = Setting::query()->orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('livewire.admin.settings.settings-page', [
            'grouped' => $grouped,
        ]);
    }

    private function formatForInput(Setting $setting): string
    {
        if ($setting->type === 'boolean') {
            return Setting::castValue($setting->value, 'boolean') ? '1' : '0';
        }

        return (string) ($setting->value ?? '');
    }

    private function normalizeForStorage(Setting $setting, string $input): string
    {
        return match ($setting->type) {
            'boolean' => $input === '1' || $input === 'true' ? '1' : '0',
            default => $input,
        };
    }
}