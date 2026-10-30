<?php

namespace App\Filament\Concerns;

use App\Support\Access;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** Resource authorization from the role permissions ("{verb} {module}") instead of one policy class per model. */
trait ChecksModulePermission
{
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        $action = $action instanceof UnitEnum ? $action->name : $action;
        $verb = match (preg_replace('/Any$/', '', $action)) {
            'view' => 'view',
            'create', 'replicate' => 'create',
            'update', 'reorder' => 'edit',
            default => 'delete', // delete, forceDelete, restore
        };

        return auth()->user()?->can(Access::permission($verb, static::$module)) ? Response::allow() : Response::deny();
    }
}
