<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailOrPhone implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (preg_match('/^09[0-9]{9}$/', $value)) {
            return true;
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'ورودی باید به صورت یک ایمیل یا شماره همراه معتبر باشد.';
    }
}
