<?php

namespace Tests\Feature\Api\FileBrowser;

use App\Services\FileBrowser\Actions\Permissions;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * @group FileBrowser
 */
class PermissionsTest extends AbstractFileBrowserTest
{
    public function test_it_browse_permissions_success(): void
    {
        $superadmin = $this->loginAsCarrierSuperAdmin();
        $prefix = $superadmin->getCompany()->getFileBrowserPrefix();
        $this->fileBrowser->setFileBrowserPrefix($prefix);

        $this->postJson(
            route('filebrowser.browse'),
            [
                'action' => Permissions::ACTION,
                'source' => 'default',
            ]
        )
            ->assertOk()
            ->assertJson(
                [
                    'success' => true,
                    'code' => 220,
                    'data' => [
                        'permissions' => [
                        ],
                    ],
                ]
            );
    }

}
