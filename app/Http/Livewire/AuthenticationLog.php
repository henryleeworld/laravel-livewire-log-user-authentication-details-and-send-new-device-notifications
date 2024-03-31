<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use JamesMills\LaravelTimezone\Facades\Timezone;
use Jenssegers\Agent\Agent;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog as Log;

class AuthenticationLog extends DataTableComponent
{
    public ?string $defaultSortColumn = 'login_at';
    public string $defaultSortDirection = 'desc';
    public string $tableName = 'authentication-log-table';

    public User $user;

    public function mount(User $user)
    {
        /*
        if (! auth()->user() || ! auth()->user()->isAdmin()) {
            $this->redirectRoute('frontend.index');
        }
        */
        $this->user = $user;
    }

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function columns(): array
    {
        return [
            Column::make(__('IP Address'), 'ip_address')
                ->searchable(),
            Column::make(__('Browser'), 'user_agent')
                ->searchable()
                ->format(function($value) {
                    $agent = tap(new Agent, fn($agent) => $agent->setUserAgent($value));
                    return $agent->platform() . ' - ' . $agent->browser();
                }),
            Column::make(__('Location'), 'location')
                ->searchable(function (Builder $query, $searchTerm) {
                    $query->orWhere('location->city', 'like', '%'.$searchTerm.'%')
                        ->orWhere('location->state', 'like', '%'.$searchTerm.'%')
                        ->orWhere('location->state_name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('location->postal_code', 'like', '%'.$searchTerm.'%');
                })
                ->format(fn ($value) => $value && $value['default'] === false ? $value['city'] . ', ' . $value['state'] : '-'),
            Column::make(__('Login At'), 'login_at')
                ->sortable()
                ->format(fn($value) => $value ? Timezone::convertToLocal($value) : '-'),
            Column::make(__('Login Successful'), 'login_successful')
                ->sortable()
                ->format(fn($value) => $value === true ? __('Yes') : __('No')),
            Column::make(__('Logout At'), 'logout_at')
                ->sortable()
                ->format(fn($value) => $value ? Timezone::convertToLocal($value) : '-'),
            Column::make(__('Cleared By User'), 'cleared_by_user')
                ->sortable()
                ->format(fn($value) => $value === true ? __('Yes') : __('No')),
        ];
    }

    public function builder(): Builder
    {
        return Log::query()
            ->where('authenticatable_type', User::class)
            ->where('authenticatable_id', $this->user->id);
    }
}
