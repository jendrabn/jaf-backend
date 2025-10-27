<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // No automatic normalization; enforce format via validation rules.
    }

    public function rules(): array
    {
        $guard = Role::getDefaultGuardName();

        $base = [
            'permissions' => ['required', 'array'],
        ];

        // Enforce lowercase snake_case: segments of lowercase letters separated by underscores.
        $snakeCaseRule = 'regex:/^[a-z]+(_[a-z]+)*$/';

        if ($this->routeIs('admin.roles.store')) {
            return array_merge($base, [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    $snakeCaseRule,
                    Rule::unique('roles', 'name')
                        ->where('guard_name', $guard),
                ],
            ]);
        } elseif ($this->routeIs('admin.roles.update')) {
            $roleParam = $this->route('role');
            $roleId = is_object($roleParam) ? $roleParam->id : $roleParam;

            return array_merge($base, [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    $snakeCaseRule,
                    Rule::unique('roles', 'name')
                        ->ignore($roleId)
                        ->where('guard_name', $guard),
                ],
            ]);
        }

        // Fallback (should not be hit if routes are named properly)
        return [
            'name' => ['required', 'string', 'max:255', $snakeCaseRule],
            'permissions' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Role name has already been taken for this guard.',
            'name.regex' => 'Role name must be lowercase snake_case (e.g., staff_orders).',
        ];
    }
}
