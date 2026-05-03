<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    /**
     * @var array<string, string>
     */
    public array $login = [
        'email'    => 'required|valid_email',
        'password' => 'required',
    ];

    /**
     * @var array<string, string>
     */
    public array $register = [
        'name'              => 'required|min_length[1]|max_length[120]',
        'email'             => 'required|valid_email|is_unique[users.email]',
        'password'          => 'required|min_length[8]',
        'password_confirm'  => 'required|matches[password]',
    ];

    /**
     * Admin user management — create user.
     *
     * @var array<string, string>
     */
    public array $userManagementCreate = [
        'display_name'     => 'required|min_length[1]|max_length[120]',
        'email'            => 'required|valid_email|is_unique[users.email]',
        'password'         => 'required|min_length[8]',
        'password_confirm' => 'required|matches[password]',
    ];
}
