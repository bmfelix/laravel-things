<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User;
use LdapRecord\Laravel\Middleware\WindowsAuthenticate;
use Tests\TestCase;

class LdapLoginTest extends TestCase
{
    use WithFaker;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_ldap_login()
    {
        DirectoryEmulator::setup('default');
        $ldapUser =  User::create([
            'cn' => $this->faker->name,
            'objectguid' => $this->faker->uuid,
            'samaccountname' => $this->faker->userName,
        ]);
        $authUser = implode('\\', [
            'custom-aluminum', $ldapUser->getFirstAttribute('samaccountname')
        ]);

        // Set the server variables for the upcoming request.
        $this->withServerVariables([
            WindowsAuthenticate::$serverKey => $authUser
        ]);

        // Attempt accessing a protected page:
        $response = $this->get('/')->assertOk();

        // Ensure the user was authenticated:
        $this->assertTrue(Auth::check());
    }
}
