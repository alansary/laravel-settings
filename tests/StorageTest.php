<?php

namespace QCod\Settings\Tests\Feature;

use QCod\Settings\Tests\TestCase;
use QCod\Settings\Setting\SettingEloquentStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StorageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var SettingEloquentStorage
     */
    protected $settingStorage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settingStorage = new SettingEloquentStorage();
    }

    /**
     * it sets a new key value in store
     *
     * @test
     */
    public function it_sets_a_new_key_value_in_store()
    {
        $this->assertDatabaseMissing('settings', ['app_name' => 'QCode']);

        $this->settingStorage->set('app_name', 'QCode');

        $this->assertDatabaseHas('settings', ['name' => 'app_name', 'val' => 'QCode']);
    }

    /**
     * it dont set if same key value pair exists in store
     *
     * @test
     */
    public function it_dont_set_if_same_key_value_pair_exists_in_store()
    {
        $this->assertDatabaseMissing('settings', ['app_name' => 'QCode']);

        $this->settingStorage->set('app_name', 'QCode');
        $this->settingStorage->set('app_name', 'QCode');

        $this->assertDatabaseHas('settings', ['name' => 'app_name', 'val' => 'QCode']);
        $this->assertCount(1, $this->settingStorage->all(true));

        $this->settingStorage->set('email_name', 'QCode');
        $this->assertCount(2, $this->settingStorage->all(true));
    }

    /**
     * it updates exisiting setting if already exists
     *
     * @test
     */
    public function it_updates_exisiting_setting_if_already_exists()
    {
        $this->settingStorage->set('app_name', 'QCode');
        $this->assertDatabaseHas('settings', ['name' => 'app_name', 'val' => 'QCode']);

        $this->settingStorage->set('app_name', 'Updated QCode');

        $this->assertDatabaseHas('settings', ['name' => 'app_name', 'val' => 'Updated QCode']);
        $this->assertEquals('Updated QCode', $this->settingStorage->get('app_name'));
    }

    /**
     * it removes a setting from storage
     *
     * @test
     */
    public function it_removes_a_setting_from_storage()
    {
        $this->settingStorage->set('app_name', 'QCode');
        $this->assertDatabaseHas('settings', ['name' => 'app_name', 'val' => 'QCode']);
        $this->assertEquals('QCode', $this->settingStorage->get('app_name'));

        $this->settingStorage->remove('app_name');

        $this->assertDatabaseMissing('settings', ['name' => 'app_name', 'val' => 'QCode']);
        $this->assertNull($this->settingStorage->get('app_name'));
    }

    /**
     * it gives default value if nothing setting not found
     *
     * @test
     */
    public function it_gives_default_value_if_nothing_setting_not_found()
    {
        $this->assertDatabaseMissing('settings', ['app_name' => 'QCode']);

        $this->assertEquals(
            'Default App Name',
            $this->settingStorage->get('app_name', 'Default App Name')
        );
    }

    /**
     * it gives you saved setting value
     *
     * @test
     */
    public function it_gives_you_saved_setting_value()
    {
        $this->settingStorage->set('app_name', 'QCode');

        $this->assertEquals(
            'QCode',
            $this->settingStorage->get('app_name', 'Default App Name')
        );

        // change the setting
        $this->settingStorage->set('app_name', 'Changed QCode');

        $this->assertEquals(
            'Changed QCode',
            $this->settingStorage->get('app_name', 'Default App Name')
        );
    }

    /**
     * it can add multiple settings in if multi array is passed
     *
     * @test
     */
    public function it_can_add_multiple_settings_in_if_multi_array_is_passed()
    {
        $this->settingStorage->set([
            'app_name' => 'QCode',
            'app_email' => 'info@email.com',
            'app_type' => 'SaaS'
        ]);

        $this->assertCount(3, $this->settingStorage->all());
        $this->assertEquals('QCode', $this->settingStorage->get('app_name'));
        $this->assertEquals('info@email.com', $this->settingStorage->get('app_email'));
        $this->assertEquals('SaaS', $this->settingStorage->get('app_type'));
    }

    /**
     * it can use helper function to set and get settings
     *
     * @test
     */
    public function it_can_use_helper_function_to_set_and_get_settings()
    {
        settings()->set('app_name', 'Cool App');

        $this->assertEquals('Cool App', settings()->get('app_name'));

        $this->assertDatabaseHas('settings', ['name' => 'app_name']);
    }

    /**
     * it can access setting via facade
     *
     * @test
     */
    public function it_can_access_setting_via_facade()
    {
        \Settings::set('app_name', 'Cool App');

        $this->assertEquals('Cool App', \Settings::get('app_name'));

        $this->assertDatabaseHas('settings', ['name' => 'app_name']);
    }
}
